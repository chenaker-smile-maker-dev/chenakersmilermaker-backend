<?php

namespace Tests\Feature;

use App\Models\Doctor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\Support\DoctorSchedulingHelpers;
use Tests\Support\RelaxedValidationService;
use Tests\TestCase;

class BookingApiTest extends TestCase
{
    use RefreshDatabase;
    use DoctorSchedulingHelpers;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->singleton(
            \Zap\Services\ValidationService::class,
            RelaxedValidationService::class,
        );

        Carbon::setTestNow(Carbon::create(2025, 12, 1, 8, 0));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /**
     * Test complete booking flow: availability check -> book appointment
     */
    public function test_complete_booking_flow(): void
    {
        ['doctor' => $doctor, 'service' => $service] = $this->createDoctorWithService();
        $patient = $this->createPatient();

        $today = Carbon::now();
        $this->addWeeklyAvailabilityForDoctor($doctor, $today, [$today->dayOfWeek], '09:00', '17:00');

        // Check availability
        $availabilityResponse = $this->getJson("/api/v1/appointement/{$doctor->id}/{$service->id}/availability");
        $availabilityResponse->assertOk();
        $this->assertNotEmpty($availabilityResponse->json('data.next_available_slots'));

        // Book appointment
        $bookingResponse = $this->actingAs($patient, 'sanctum')
            ->postJson("/api/v1/booking/{$doctor->id}/{$service->id}/book", [
                'date' => $today->format('d-m-Y'),
                'start_time' => '10:00',
            ]);

        $bookingResponse->assertOk();
        $bookingResponse->assertJsonPath('success', true);

        // Verify appointment created
        $appointmentId = $bookingResponse->json('data.appointment_id');
        $this->assertDatabaseHas('appointments', [
            'id' => $appointmentId,
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
        ]);

        // Verify Zap sync created blocked schedule
        $this->assertDatabaseHas('schedules', [
            'schedulable_type' => Doctor::class,
            'schedulable_id' => $doctor->id,
            'name' => 'Appointment #'.$appointmentId,
            'schedule_type' => 'blocked',
        ]);
    }
}

