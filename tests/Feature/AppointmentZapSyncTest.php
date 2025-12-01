<?php

namespace Tests\Feature;

use App\Enums\Appointment\AppointmentStatus;
use App\Models\Doctor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\Support\DoctorSchedulingHelpers;
use Tests\Support\RelaxedValidationService;
use Tests\TestCase;

class AppointmentZapSyncTest extends TestCase
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

        Carbon::setTestNow(Carbon::create(2025, 12, 1, 8, 0)); // Monday 8:00 AM
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /**
     * Test booking creates appointment in database
     */
    public function test_booking_creates_appointment_in_database(): void
    {
        ['doctor' => $doctor, 'service' => $service] = $this->createDoctorWithService();
        $patient = $this->createPatient();

        $today = Carbon::now();
        $this->addWeeklyAvailabilityForDoctor($doctor, $today, [$today->dayOfWeek], '09:00', '17:00');

        $payload = [
            'date' => $today->format('d-m-Y'),
            'start_time' => '10:00',
        ];

        $this->actingAs($patient, 'sanctum')
            ->postJson("/api/v1/booking/{$doctor->id}/{$service->id}/book", $payload)
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('appointments', [
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'service_id' => $service->id,
        ]);
    }

    /**
     * Test booking creates a blocked Zap schedule for the appointment
     */
    public function test_booking_creates_zap_blocked_schedule(): void
    {
        ['doctor' => $doctor, 'service' => $service] = $this->createDoctorWithService();
        $patient = $this->createPatient();

        $today = Carbon::now();
        $this->addWeeklyAvailabilityForDoctor($doctor, $today, [$today->dayOfWeek], '09:00', '17:00');

        $payload = [
            'date' => $today->format('d-m-Y'),
            'start_time' => '11:00',
        ];

        $response = $this->actingAs($patient, 'sanctum')
            ->postJson("/api/v1/booking/{$doctor->id}/{$service->id}/book", $payload);

        $appointmentId = $response->json('data.appointment_id');

        $this->assertDatabaseHas('schedules', [
            'schedulable_type' => Doctor::class,
            'schedulable_id' => $doctor->id,
            'name' => 'Appointment #'.$appointmentId,
            'schedule_type' => 'blocked',
        ]);
    }

    /**
     * Test the blocked schedule has correct time period
     */
    public function test_zap_blocked_schedule_has_correct_time_period(): void
    {
        ['doctor' => $doctor, 'service' => $service] = $this->createDoctorWithService();
        $patient = $this->createPatient();

        $today = Carbon::now();
        $this->addWeeklyAvailabilityForDoctor($doctor, $today, [$today->dayOfWeek], '09:00', '17:00');

        $payload = [
            'date' => $today->format('d-m-Y'),
            'start_time' => '14:00',
        ];

        $response = $this->actingAs($patient, 'sanctum')
            ->postJson("/api/v1/booking/{$doctor->id}/{$service->id}/book", $payload);

        $appointmentId = $response->json('data.appointment_id');

        $blockedSchedule = $doctor->schedules()
            ->where('name', 'Appointment #'.$appointmentId)
            ->first();

        $this->assertNotNull($blockedSchedule);

        $period = $blockedSchedule->periods()->first();
        $this->assertNotNull($period);
        // Times are stored with seconds, so we compare without seconds
        $this->assertStringStartsWith('14:00', $period->start_time);
        $this->assertStringStartsWith('14:30', $period->end_time); // 30 min default service duration
    }

    /**
     * Test second booking at same time fails (double-booking prevention)
     */
    public function test_prevents_double_booking_same_time(): void
    {
        ['doctor' => $doctor, 'service' => $service] = $this->createDoctorWithService();
        $patient1 = $this->createPatient();
        $patient2 = $this->createPatient();

        $today = Carbon::now();
        $this->addWeeklyAvailabilityForDoctor($doctor, $today, [$today->dayOfWeek], '09:00', '17:00');

        $payload = [
            'date' => $today->format('d-m-Y'),
            'start_time' => '15:00',
        ];

        // First booking should succeed
        $this->actingAs($patient1, 'sanctum')
            ->postJson("/api/v1/booking/{$doctor->id}/{$service->id}/book", $payload)
            ->assertOk();

        // Second booking at same time should fail
        $response = $this->actingAs($patient2, 'sanctum')
            ->postJson("/api/v1/booking/{$doctor->id}/{$service->id}/book", $payload);

        $response->assertStatus(422); // Validation error
    }

    /**
     * Test booking succeeds when time slot is adjacent to existing appointment
     */
    public function test_allows_back_to_back_bookings(): void
    {
        ['doctor' => $doctor, 'service' => $service] = $this->createDoctorWithService();
        $patient1 = $this->createPatient();
        $patient2 = $this->createPatient();

        $today = Carbon::now();
        $this->addWeeklyAvailabilityForDoctor($doctor, $today, [$today->dayOfWeek], '09:00', '17:00');

        // First booking at 10:00
        $this->actingAs($patient1, 'sanctum')
            ->postJson("/api/v1/booking/{$doctor->id}/{$service->id}/book", [
                'date' => $today->format('d-m-Y'),
                'start_time' => '10:00',
            ])->assertOk();

        // Second booking at 10:30 (right after first) should succeed
        $this->actingAs($patient2, 'sanctum')
            ->postJson("/api/v1/booking/{$doctor->id}/{$service->id}/book", [
                'date' => $today->format('d-m-Y'),
                'start_time' => '10:30',
            ])->assertOk();

        // Verify both appointments exist
        $appointments = $doctor->appointments()->get();
        $this->assertCount(2, $appointments);
    }

    /**
     * Test booking respects service duration when checking availability
     */
    public function test_booking_validates_slot_duration_matches_service(): void
    {
        $doctor = $this->createDoctor();
        $service = $this->createService(['duration' => 60]); // 60 minute service
        $doctor->services()->attach($service);

        $patient = $this->createPatient();

        $today = Carbon::now();
        $this->addWeeklyAvailabilityForDoctor($doctor, $today, [$today->dayOfWeek], '09:00', '17:00');

        $payload = [
            'date' => $today->format('d-m-Y'),
            'start_time' => '16:30', // Only 30 min left before 17:00 close
        ];

        $response = $this->actingAs($patient, 'sanctum')
            ->postJson("/api/v1/booking/{$doctor->id}/{$service->id}/book", $payload);

        // Should fail because 60 min service doesn't fit in remaining time
        $response->assertStatus(422);
    }

    /**
     * Test appointment created with pending status
     */
    public function test_appointment_created_with_pending_status(): void
    {
        ['doctor' => $doctor, 'service' => $service] = $this->createDoctorWithService();
        $patient = $this->createPatient();

        $today = Carbon::now();
        $this->addWeeklyAvailabilityForDoctor($doctor, $today, [$today->dayOfWeek], '09:00', '17:00');

        $this->actingAs($patient, 'sanctum')
            ->postJson("/api/v1/booking/{$doctor->id}/{$service->id}/book", [
                'date' => $today->format('d-m-Y'),
                'start_time' => '13:00',
            ])->assertOk();

        $this->assertDatabaseHas('appointments', [
            'doctor_id' => $doctor->id,
            'status' => AppointmentStatus::PENDING->value,
        ]);
    }

    /**
     * Test multiple appointments on different times don't interfere
     */
    public function test_multiple_appointments_different_times_all_tracked(): void
    {
        ['doctor' => $doctor, 'service' => $service] = $this->createDoctorWithService();

        $today = Carbon::now();
        $this->addWeeklyAvailabilityForDoctor($doctor, $today, [$today->dayOfWeek], '09:00', '17:00');

        $patient1 = $this->createPatient();
        $patient2 = $this->createPatient();
        $patient3 = $this->createPatient();

        $times = ['09:00', '10:00', '11:00'];
        $patients = [$patient1, $patient2, $patient3];

        foreach ($times as $index => $time) {
            $this->actingAs($patients[$index], 'sanctum')
                ->postJson("/api/v1/booking/{$doctor->id}/{$service->id}/book", [
                    'date' => $today->format('d-m-Y'),
                    'start_time' => $time,
                ])->assertOk();
        }

        // Verify all 3 appointments exist
        $appointments = $doctor->appointments()->get();
        $this->assertCount(3, $appointments);
    }

    /**
     * Test appointment metadata includes Zap sync info
     */
    public function test_appointment_metadata_includes_zap_sync_info(): void
    {
        ['doctor' => $doctor, 'service' => $service] = $this->createDoctorWithService();
        $patient = $this->createPatient();

        $today = Carbon::now();
        $this->addWeeklyAvailabilityForDoctor($doctor, $today, [$today->dayOfWeek], '09:00', '17:00');

        $response = $this->actingAs($patient, 'sanctum')
            ->postJson("/api/v1/booking/{$doctor->id}/{$service->id}/book", [
                'date' => $today->format('d-m-Y'),
                'start_time' => '15:00',
            ]);

        $appointmentId = $response->json('data.appointment_id');

        $appointment = $doctor->appointments()->find($appointmentId);
        $this->assertNotNull($appointment);
        $this->assertArrayHasKey('metadata', $appointment->toArray());
        $this->assertArrayHasKey('booked_at', $appointment->metadata);
        $this->assertArrayHasKey('zap_block_created', $appointment->metadata);
    }
}
