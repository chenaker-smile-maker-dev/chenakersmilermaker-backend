<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\Support\DoctorSchedulingHelpers;
use Tests\Support\RelaxedValidationService;
use Tests\TestCase;

class DoctorAvailabilityTest extends TestCase
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
     * Test that availability endpoint returns multiple slots when doctor has availability
     */
    public function test_returns_multiple_available_slots(): void
    {
        ['doctor' => $doctor, 'service' => $service] = $this->createDoctorWithService();

        $today = Carbon::now();
        $this->addWeeklyAvailabilityForDoctor($doctor, $today, [$today->dayOfWeek], '09:00', '17:00');

        $response = $this->getJson("/api/v1/appointement/{$doctor->id}/{$service->id}/availability");

        $response->assertOk();
        $slots = $response->json('data.next_available_slots');

        $this->assertIsArray($slots);
        $this->assertGreaterThan(0, count($slots), 'Should have at least one slot');
        $this->assertLessThanOrEqual(5, count($slots), 'Should have at most 5 slots');

        // Verify slot structure
        $this->assertArrayHasKey('start_time', $slots[0]);
        $this->assertArrayHasKey('end_time', $slots[0]);
        $this->assertArrayHasKey('date', $slots[0]);
    }

    /**
     * Test availability when doctor has no schedule configured
     */
    public function test_returns_empty_when_no_availability_scheduled(): void
    {
        ['doctor' => $doctor, 'service' => $service] = $this->createDoctorWithService();

        // No availability schedule added

        $response = $this->getJson("/api/v1/appointement/{$doctor->id}/{$service->id}/availability");

        $response->assertOk();
        $response->assertJsonPath('data.next_available_slot', null);
        $response->assertJsonPath('data.next_available_slots', []);
        $response->assertJsonPath('data.message', 'Doctor has no availability scheduled');
    }

    /**
     * Test slots account for existing appointments blocking time
     */
    public function test_availability_excludes_appointment_blocked_slots(): void
    {
        ['doctor' => $doctor, 'service' => $service] = $this->createDoctorWithService();
        $patient = $this->createPatient();

        $today = Carbon::now();
        $this->addWeeklyAvailabilityForDoctor($doctor, $today, [$today->dayOfWeek], '09:00', '17:00');

        // Create an appointment that blocks 10:00-10:30
        $this->actingAs($patient, 'sanctum')
            ->postJson("/api/v1/booking/{$doctor->id}/{$service->id}/book", [
                'date' => $today->format('d-m-Y'),
                'start_time' => '10:00',
            ])->assertOk();

        $response = $this->getJson("/api/v1/appointement/{$doctor->id}/{$service->id}/availability");

        $response->assertOk();
        $slots = $response->json('data.next_available_slots');

        // Verify the 10:00 slot is not available
        $blockedSlotExists = collect($slots)->contains(function ($slot) {
            return $slot['start_time'] === '10:00';
        });

        $this->assertFalse($blockedSlotExists, 'Blocked slot 10:00 should not be available');
    }

    /**
     * Test slots account for explicitly blocked schedules
     */
    public function test_availability_respects_explicit_blocked_times(): void
    {
        ['doctor' => $doctor, 'service' => $service] = $this->createDoctorWithService();

        $today = Carbon::now();
        $this->addWeeklyAvailabilityForDoctor($doctor, $today, [$today->dayOfWeek], '09:00', '17:00');

        // Add a blocked schedule (e.g., doctor lunch break)
        $this->blockDoctorDuring($doctor, $today, '12:00', '13:00');

        $response = $this->getJson("/api/v1/appointement/{$doctor->id}/{$service->id}/availability");

        $response->assertOk();
        $slots = $response->json('data.next_available_slots');

        // Verify 12:00 slot is blocked
        $blockedSlotExists = collect($slots)->contains(function ($slot) {
            return $slot['start_time'] === '12:00';
        });

        $this->assertFalse($blockedSlotExists, 'Blocked 12:00 slot should not be available');
    }

    /**
     * Test slots return slots for available hours only
     */
    public function test_availability_respects_office_hours(): void
    {
        ['doctor' => $doctor, 'service' => $service] = $this->createDoctorWithService();

        $today = Carbon::now();
        // Doctor available only 10:00-12:00 (2 hour window = 4 slots of 30 min each)
        $this->addWeeklyAvailabilityForDoctor($doctor, $today, [$today->dayOfWeek], '10:00', '12:00');

        $response = $this->getJson("/api/v1/appointement/{$doctor->id}/{$service->id}/availability");

        $response->assertOk();
        $slots = $response->json('data.next_available_slots');

        // All slots should be within office hours
        foreach ($slots as $slot) {
            $this->assertGreaterThanOrEqual('10:00', $slot['start_time']);
            $this->assertLessThanOrEqual('12:00', $slot['end_time']);
        }
    }

    /**
     * Test next_available_slot points to the first slot
     */
    public function test_next_available_slot_is_first_in_array(): void
    {
        ['doctor' => $doctor, 'service' => $service] = $this->createDoctorWithService();

        $today = Carbon::now();
        $this->addWeeklyAvailabilityForDoctor($doctor, $today, [$today->dayOfWeek], '09:00', '17:00');

        $response = $this->getJson("/api/v1/appointement/{$doctor->id}/{$service->id}/availability");

        $response->assertOk();
        $nextSlot = $response->json('data.next_available_slot');
        $slots = $response->json('data.next_available_slots');

        $this->assertNotNull($nextSlot);
        $this->assertEquals($nextSlot, $slots[0], 'next_available_slot should equal first slot');
    }

    /**
     * Test response includes doctor and service metadata
     */
    public function test_response_includes_doctor_and_service_metadata(): void
    {
        ['doctor' => $doctor, 'service' => $service] = $this->createDoctorWithService();

        $today = Carbon::now();
        $this->addWeeklyAvailabilityForDoctor($doctor, $today, [$today->dayOfWeek], '09:00', '17:00');

        $response = $this->getJson("/api/v1/appointement/{$doctor->id}/{$service->id}/availability");

        $response->assertOk();
        $response->assertJsonPath('data.id', $doctor->id);
        $response->assertJsonPath('data.doctor_name', $doctor->name);
        $response->assertJsonPath('data.service_id', $service->id);
        $response->assertJsonPath('data.service_name', $service->name);
        $response->assertJsonPath('data.service_duration_minutes', 30);
        $response->assertJsonPath('data.is_service_active', true);
    }
}
