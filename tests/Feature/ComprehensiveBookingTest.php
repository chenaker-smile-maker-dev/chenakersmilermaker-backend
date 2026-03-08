<?php

namespace Tests\Feature;

use App\Enums\Appointment\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\Support\DoctorSchedulingHelpers;
use Tests\Support\RelaxedValidationService;
use Tests\TestCase;

class ComprehensiveBookingTest extends TestCase
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

    // ==================== VALIDATION TESTS ====================

    /**
     * Test booking fails when service is inactive
     */
    public function test_cannot_book_inactive_service(): void
    {
        $doctor = $this->createDoctor();
        $service = $this->createService(['active' => false]);
        $doctor->services()->attach($service);
        $patient = $this->createPatient();

        $today = Carbon::now();
        $this->addWeeklyAvailabilityForDoctor($doctor, $today, [$today->dayOfWeek], '09:00', '17:00');

        $response = $this->actingAs($patient, 'sanctum')
            ->postJson("/api/v1/booking/{$doctor->id}/{$service->id}/book", [
                'date' => $today->format('d-m-Y'),
                'start_time' => '10:00',
            ]);

        $response->assertStatus(422);
    }

    /**
     * Test booking fails when doctor doesn't provide service
     */
    public function test_cannot_book_service_doctor_doesnt_provide(): void
    {
        $doctor = $this->createDoctor();
        $service = $this->createService();
        // Don't attach service to doctor
        $patient = $this->createPatient();

        $today = Carbon::now();
        $this->addWeeklyAvailabilityForDoctor($doctor, $today, [$today->dayOfWeek], '09:00', '17:00');

        $response = $this->actingAs($patient, 'sanctum')
            ->postJson("/api/v1/booking/{$doctor->id}/{$service->id}/book", [
                'date' => $today->format('d-m-Y'),
                'start_time' => '10:00',
            ]);

        $response->assertStatus(422);
    }

    /**
     * Test booking fails for past dates
     */
    public function test_cannot_book_past_dates(): void
    {
        ['doctor' => $doctor, 'service' => $service] = $this->createDoctorWithService();
        $patient = $this->createPatient();

        $today = Carbon::now();
        $this->addWeeklyAvailabilityForDoctor($doctor, $today, [$today->dayOfWeek], '09:00', '17:00');

        $yesterday = $today->copy()->subDay();

        $response = $this->actingAs($patient, 'sanctum')
            ->postJson("/api/v1/booking/{$doctor->id}/{$service->id}/book", [
                'date' => $yesterday->format('d-m-Y'),
                'start_time' => '10:00',
            ]);

        $response->assertStatus(422);
    }

    // ==================== AVAILABILITY CHECK TESTS ====================

    /**
     * Test availability check endpoint returns correct response structure
     */
    public function test_availability_check_returns_correct_structure(): void
    {
        ['doctor' => $doctor, 'service' => $service] = $this->createDoctorWithService();

        $today = Carbon::now();
        $this->addWeeklyAvailabilityForDoctor($doctor, $today, [$today->dayOfWeek], '09:00', '17:00');

        $response = $this->postJson("/api/v1/booking/{$doctor->id}/{$service->id}/check-availability", [
            'date' => $today->format('d-m-Y'),
            'start_time' => '10:00',
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'data' => [
                'available',
                'doctor_id',
                'service_id',
                'date',
                'start_time',
                'end_time',
                'duration_minutes',
            ],
        ]);
    }

    /**
     * Test availability check returns unavailable for booked slot
     */
    public function test_availability_check_returns_unavailable_for_booked_slot(): void
    {
        ['doctor' => $doctor, 'service' => $service] = $this->createDoctorWithService();
        $patient = $this->createPatient();

        $today = Carbon::now();
        $this->addWeeklyAvailabilityForDoctor($doctor, $today, [$today->dayOfWeek], '09:00', '17:00');

        // Book an appointment
        $this->actingAs($patient, 'sanctum')
            ->postJson("/api/v1/booking/{$doctor->id}/{$service->id}/book", [
                'date' => $today->format('d-m-Y'),
                'start_time' => '10:00',
            ])->assertOk();

        // Check availability for same slot
        $response = $this->postJson("/api/v1/booking/{$doctor->id}/{$service->id}/check-availability", [
            'date' => $today->format('d-m-Y'),
            'start_time' => '10:00',
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.available', false);
    }

    /**
     * Test availability check outside working hours
     */
    public function test_availability_check_outside_office_hours(): void
    {
        ['doctor' => $doctor, 'service' => $service] = $this->createDoctorWithService();

        $today = Carbon::now();
        $this->addWeeklyAvailabilityForDoctor($doctor, $today, [$today->dayOfWeek], '09:00', '17:00');

        // Check 8:00 AM (before office hours)
        $response = $this->postJson("/api/v1/booking/{$doctor->id}/{$service->id}/check-availability", [
            'date' => $today->format('d-m-Y'),
            'start_time' => '08:00',
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.available', false);

        // Check 6:00 PM (after office hours)
        $response = $this->postJson("/api/v1/booking/{$doctor->id}/{$service->id}/check-availability", [
            'date' => $today->format('d-m-Y'),
            'start_time' => '18:00',
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.available', false);
    }

    // ==================== SLOT GENERATION TESTS ====================

    /**
     * Test slots are generated at correct intervals matching service duration
     */
    public function test_slots_generated_at_service_duration_intervals(): void
    {
        $doctor = $this->createDoctor();
        $service = $this->createService(['duration' => 45]); // 45-minute service
        $doctor->services()->attach($service);

        $today = Carbon::now();
        $this->addWeeklyAvailabilityForDoctor($doctor, $today, [$today->dayOfWeek], '09:00', '12:00');

        $response = $this->getJson("/api/v1/appointement/{$doctor->id}/{$service->id}/availability");
        $slots = $response->json('data.next_available_slots');

        $this->assertNotEmpty($slots);
        
        // Check that each slot has the correct duration
        foreach ($slots as $slot) {
            $start = Carbon::createFromFormat('H:i', $slot['start_time']);
            $end = Carbon::createFromFormat('H:i', $slot['end_time']);
            $duration = $start->diffInMinutes($end);
            $this->assertEquals(45, $duration, 'Each slot should match service duration of 45 minutes');
        }
        
        // Verify slots from same day are consecutive
        $sameDaySlots = collect($slots)->filter(function ($slot) use ($slots) {
            return $slot['date'] === $slots[0]['date'];
        })->values();
        
        for ($i = 1; $i < $sameDaySlots->count(); $i++) {
            $prevEnd = Carbon::createFromFormat('H:i', $sameDaySlots[$i - 1]['end_time']);
            $currentStart = Carbon::createFromFormat('H:i', $sameDaySlots[$i]['start_time']);
            
            $this->assertEquals(
                $prevEnd->format('H:i'), 
                $currentStart->format('H:i'), 
                'Same-day slots should be consecutive'
            );
        }
    }

    /**
     * Test slots respect service duration when checking end of day
     */
    public function test_slots_dont_exceed_office_hours(): void
    {
        $doctor = $this->createDoctor();
        $service = $this->createService(['duration' => 60]); // 60-minute service
        $doctor->services()->attach($service);

        $today = Carbon::now();
        // Office hours: 09:00-12:00 (3 hours = 3 slots of 60 min each)
        $this->addWeeklyAvailabilityForDoctor($doctor, $today, [$today->dayOfWeek], '09:00', '12:00');

        $response = $this->getJson("/api/v1/appointement/{$doctor->id}/{$service->id}/availability");
        $slots = $response->json('data.next_available_slots');

        $this->assertNotEmpty($slots);
        
        // All slots should end by 12:00
        foreach ($slots as $slot) {
            $this->assertLessThanOrEqual('12:00', $slot['end_time'], 'Slot should not exceed office hours');
        }
        
        // Slots for today should be at most 3: 09:00, 10:00, 11:00
        $todaySlots = collect($slots)->filter(function ($slot) use ($today) {
            return $slot['date'] === $today->format('Y-m-d');
        });
        $this->assertLessThanOrEqual(3, $todaySlots->count(), 'Should not exceed available slots for today');
    }

    // ==================== MULTIPLE DOCTORS TESTS ====================

    /**
     * Test multiple doctors can have appointments at the same time
     */
    public function test_multiple_doctors_can_book_same_time(): void
    {
        // Create two doctors with the same service
        $doctor1 = $this->createDoctor();
        $doctor2 = $this->createDoctor();
        $service = $this->createService();
        $doctor1->services()->attach($service);
        $doctor2->services()->attach($service);

        $patient1 = $this->createPatient();
        $patient2 = $this->createPatient();

        $today = Carbon::now();
        $this->addWeeklyAvailabilityForDoctor($doctor1, $today, [$today->dayOfWeek], '09:00', '17:00');
        $this->addWeeklyAvailabilityForDoctor($doctor2, $today, [$today->dayOfWeek], '09:00', '17:00');

        // Both patients book the same time with different doctors
        $this->actingAs($patient1, 'sanctum')
            ->postJson("/api/v1/booking/{$doctor1->id}/{$service->id}/book", [
                'date' => $today->format('d-m-Y'),
                'start_time' => '10:00',
            ])->assertOk();

        $this->actingAs($patient2, 'sanctum')
            ->postJson("/api/v1/booking/{$doctor2->id}/{$service->id}/book", [
                'date' => $today->format('d-m-Y'),
                'start_time' => '10:00',
            ])->assertOk();

        // Both appointments should exist
        $this->assertDatabaseHas('appointments', [
            'doctor_id' => $doctor1->id,
            'patient_id' => $patient1->id,
        ]);

        $this->assertDatabaseHas('appointments', [
            'doctor_id' => $doctor2->id,
            'patient_id' => $patient2->id,
        ]);
    }

    /**
     * Test each doctor's availability is independent
     */
    public function test_doctor_availabilities_are_independent(): void
    {
        $doctor1 = $this->createDoctor();
        $doctor2 = $this->createDoctor();
        $service = $this->createService();
        $doctor1->services()->attach($service);
        $doctor2->services()->attach($service);

        $today = Carbon::now();
        
        // Doctor 1 available 09:00-12:00
        $this->addWeeklyAvailabilityForDoctor($doctor1, $today, [$today->dayOfWeek], '09:00', '12:00');
        
        // Doctor 2 available 14:00-17:00
        $this->addWeeklyAvailabilityForDoctor($doctor2, $today, [$today->dayOfWeek], '14:00', '17:00');

        // Get availability for both
        $response1 = $this->getJson("/api/v1/appointement/{$doctor1->id}/{$service->id}/availability");
        $response2 = $this->getJson("/api/v1/appointement/{$doctor2->id}/{$service->id}/availability");

        $slots1 = $response1->json('data.next_available_slots');
        $slots2 = $response2->json('data.next_available_slots');

        // Doctor 1 should have morning slots
        $this->assertNotEmpty($slots1);
        $this->assertGreaterThanOrEqual('09:00', $slots1[0]['start_time']);
        $this->assertLessThanOrEqual('12:00', $slots1[0]['start_time']);

        // Doctor 2 should have afternoon slots
        $this->assertNotEmpty($slots2);
        $this->assertGreaterThanOrEqual('14:00', $slots2[0]['start_time']);
        $this->assertLessThanOrEqual('17:00', $slots2[0]['start_time']);
    }

    // ==================== DAY OF WEEK TESTS ====================

    /**
     * Test availability only returns slots on allowed days
     */
    public function test_availability_only_on_allowed_days(): void
    {
        ['doctor' => $doctor, 'service' => $service] = $this->createDoctorWithService();

        // Set today as Monday (day 1)
        $today = Carbon::now(); // Monday
        $this->assertEquals(1, $today->dayOfWeek, 'Test should run on Monday');

        // Doctor only available on Tuesday (day 2)
        $this->addWeeklyAvailabilityForDoctor($doctor, $today, [2], '09:00', '17:00');

        $response = $this->getJson("/api/v1/appointement/{$doctor->id}/{$service->id}/availability");
        $slots = $response->json('data.next_available_slots');

        $this->assertNotEmpty($slots);
        
        // All slots should be for tomorrow (Tuesday), not today
        foreach ($slots as $slot) {
            $slotDate = Carbon::parse($slot['date']);
            $this->assertEquals(2, $slotDate->dayOfWeek, 'Slots should only be on Tuesday');
        }
    }

    /**
     * Test booking fails on non-available days
     */
    public function test_cannot_book_on_non_available_days(): void
    {
        ['doctor' => $doctor, 'service' => $service] = $this->createDoctorWithService();
        $patient = $this->createPatient();

        $today = Carbon::now(); // Monday (day 1)
        
        // Doctor only available on Tuesday (day 2)
        $this->addWeeklyAvailabilityForDoctor($doctor, $today, [2], '09:00', '17:00');

        // Try to book for Monday (today)
        $response = $this->actingAs($patient, 'sanctum')
            ->postJson("/api/v1/booking/{$doctor->id}/{$service->id}/book", [
                'date' => $today->format('d-m-Y'),
                'start_time' => '10:00',
            ]);

        $response->assertStatus(422);
    }

    // ==================== OVERLAPPING TESTS ====================

    /**
     * Test overlapping appointments are prevented within same doctor
     */
    public function test_overlapping_appointments_prevented(): void
    {
        ['doctor' => $doctor, 'service' => $service] = $this->createDoctorWithService();
        $patient1 = $this->createPatient();
        $patient2 = $this->createPatient();

        $today = Carbon::now();
        $this->addWeeklyAvailabilityForDoctor($doctor, $today, [$today->dayOfWeek], '09:00', '17:00');

        // Patient 1 books 10:00-10:30 (30 min service)
        $this->actingAs($patient1, 'sanctum')
            ->postJson("/api/v1/booking/{$doctor->id}/{$service->id}/book", [
                'date' => $today->format('d-m-Y'),
                'start_time' => '10:00',
            ])->assertOk();

        // Patient 2 tries to book 10:15 (overlaps with first appointment)
        $response = $this->actingAs($patient2, 'sanctum')
            ->postJson("/api/v1/booking/{$doctor->id}/{$service->id}/book", [
                'date' => $today->format('d-m-Y'),
                'start_time' => '10:15',
            ]);

        $response->assertStatus(422);
    }

    /**
     * Test adjacent appointments are allowed (end time = start time)
     */
    public function test_adjacent_appointments_allowed(): void
    {
        ['doctor' => $doctor, 'service' => $service] = $this->createDoctorWithService();
        $patient1 = $this->createPatient();
        $patient2 = $this->createPatient();

        $today = Carbon::now();
        $this->addWeeklyAvailabilityForDoctor($doctor, $today, [$today->dayOfWeek], '09:00', '17:00');

        // Patient 1 books 10:00-10:30
        $this->actingAs($patient1, 'sanctum')
            ->postJson("/api/v1/booking/{$doctor->id}/{$service->id}/book", [
                'date' => $today->format('d-m-Y'),
                'start_time' => '10:00',
            ])->assertOk();

        // Patient 2 books exactly at 10:30 (should be allowed)
        $this->actingAs($patient2, 'sanctum')
            ->postJson("/api/v1/booking/{$doctor->id}/{$service->id}/book", [
                'date' => $today->format('d-m-Y'),
                'start_time' => '10:30',
            ])->assertOk();

        $this->assertEquals(2, $doctor->appointments()->count());
    }

    // ==================== APPOINTMENT DATA TESTS ====================

    /**
     * Test appointment stores correct data
     */
    public function test_appointment_stores_correct_data(): void
    {
        ['doctor' => $doctor, 'service' => $service] = $this->createDoctorWithService(
            [],
            ['price' => 2500]
        );
        $patient = $this->createPatient();

        $today = Carbon::now();
        $this->addWeeklyAvailabilityForDoctor($doctor, $today, [$today->dayOfWeek], '09:00', '17:00');

        $response = $this->actingAs($patient, 'sanctum')
            ->postJson("/api/v1/booking/{$doctor->id}/{$service->id}/book", [
                'date' => $today->format('d-m-Y'),
                'start_time' => '10:30',
            ]);

        $response->assertOk();
        $appointmentId = $response->json('data.appointment_id');

        $appointment = Appointment::find($appointmentId);
        
        $this->assertEquals($doctor->id, $appointment->doctor_id);
        $this->assertEquals($service->id, $appointment->service_id);
        $this->assertEquals($patient->id, $appointment->patient_id);
        $this->assertEquals(2500, $appointment->price);
        $this->assertEquals(AppointmentStatus::PENDING, $appointment->status);
        $this->assertEquals('10:30', $appointment->from->format('H:i'));
        $this->assertEquals('11:00', $appointment->to->format('H:i'));
    }

    /**
     * Test appointment response includes all required fields
     */
    public function test_booking_response_includes_all_fields(): void
    {
        ['doctor' => $doctor, 'service' => $service] = $this->createDoctorWithService();
        $patient = $this->createPatient();

        $today = Carbon::now();
        $this->addWeeklyAvailabilityForDoctor($doctor, $today, [$today->dayOfWeek], '09:00', '17:00');

        $response = $this->actingAs($patient, 'sanctum')
            ->postJson("/api/v1/booking/{$doctor->id}/{$service->id}/book", [
                'date' => $today->format('d-m-Y'),
                'start_time' => '14:00',
            ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'data' => [
                'appointment_id',
                'appointment_status',
                'doctor_name',
                'service_name',
                'date',
                'start_time',
                'end_time',
                'price',
            ],
            'message',
        ]);
    }

    // ==================== EDGE CASE TESTS ====================

    /**
     * Test booking at exact office start time
     */
    public function test_can_book_at_exact_office_start(): void
    {
        ['doctor' => $doctor, 'service' => $service] = $this->createDoctorWithService();
        $patient = $this->createPatient();

        $today = Carbon::now();
        $this->addWeeklyAvailabilityForDoctor($doctor, $today, [$today->dayOfWeek], '09:00', '17:00');

        $response = $this->actingAs($patient, 'sanctum')
            ->postJson("/api/v1/booking/{$doctor->id}/{$service->id}/book", [
                'date' => $today->format('d-m-Y'),
                'start_time' => '09:00',
            ]);

        $response->assertOk();
    }

    /**
     * Test booking at last possible slot of the day
     */
    public function test_can_book_at_last_slot_of_day(): void
    {
        ['doctor' => $doctor, 'service' => $service] = $this->createDoctorWithService();
        $patient = $this->createPatient();

        $today = Carbon::now();
        // Office hours: 09:00-17:00 with 30 min service = last slot at 16:30
        $this->addWeeklyAvailabilityForDoctor($doctor, $today, [$today->dayOfWeek], '09:00', '17:00');

        $response = $this->actingAs($patient, 'sanctum')
            ->postJson("/api/v1/booking/{$doctor->id}/{$service->id}/book", [
                'date' => $today->format('d-m-Y'),
                'start_time' => '16:30',
            ]);

        $response->assertOk();
    }

    /**
     * Test booking fails when slot would exceed office hours
     */
    public function test_cannot_book_slot_exceeding_office_hours(): void
    {
        ['doctor' => $doctor, 'service' => $service] = $this->createDoctorWithService();
        $patient = $this->createPatient();

        $today = Carbon::now();
        // Office hours: 09:00-17:00, trying to book at 16:45 (would end at 17:15)
        $this->addWeeklyAvailabilityForDoctor($doctor, $today, [$today->dayOfWeek], '09:00', '17:00');

        $response = $this->actingAs($patient, 'sanctum')
            ->postJson("/api/v1/booking/{$doctor->id}/{$service->id}/book", [
                'date' => $today->format('d-m-Y'),
                'start_time' => '16:45',
            ]);

        $response->assertStatus(422);
    }

    /**
     * Test availability slots skip past times on current day
     */
    public function test_availability_skips_past_times_on_current_day(): void
    {
        ['doctor' => $doctor, 'service' => $service] = $this->createDoctorWithService();

        // Set current time to 10:00 AM
        Carbon::setTestNow(Carbon::create(2025, 12, 1, 10, 0));
        $today = Carbon::now();

        $this->addWeeklyAvailabilityForDoctor($doctor, $today, [$today->dayOfWeek], '09:00', '17:00');

        $response = $this->getJson("/api/v1/appointement/{$doctor->id}/{$service->id}/availability");
        $slots = $response->json('data.next_available_slots');

        $this->assertNotEmpty($slots);
        
        // First slot should be at or after 10:00, not 09:00 or 09:30
        $firstSlotTime = $slots[0]['start_time'];
        $this->assertGreaterThanOrEqual('10:00', $firstSlotTime, 'Should not offer past times');
    }

    // ==================== BLOCKED TIMES TESTS ====================

    /**
     * Test blocked time prevents booking during that period
     */
    public function test_blocked_time_prevents_booking(): void
    {
        ['doctor' => $doctor, 'service' => $service] = $this->createDoctorWithService();
        $patient = $this->createPatient();

        $today = Carbon::now();
        $this->addWeeklyAvailabilityForDoctor($doctor, $today, [$today->dayOfWeek], '09:00', '17:00');
        
        // Block 12:00-13:00 (lunch break)
        $this->blockDoctorDuring($doctor, $today, '12:00', '13:00');

        // Try to book at 12:00
        $response = $this->actingAs($patient, 'sanctum')
            ->postJson("/api/v1/booking/{$doctor->id}/{$service->id}/book", [
                'date' => $today->format('d-m-Y'),
                'start_time' => '12:00',
            ]);

        $response->assertStatus(422);
    }

    /**
     * Test blocked time at start of day
     */
    public function test_blocked_time_at_start_of_day(): void
    {
        ['doctor' => $doctor, 'service' => $service] = $this->createDoctorWithService();
        $patient = $this->createPatient();

        $today = Carbon::now();
        $this->addWeeklyAvailabilityForDoctor($doctor, $today, [$today->dayOfWeek], '09:00', '17:00');
        
        // Block 09:00-10:00
        $this->blockDoctorDuring($doctor, $today, '09:00', '10:00');

        // Try to book at 09:00
        $response = $this->actingAs($patient, 'sanctum')
            ->postJson("/api/v1/booking/{$doctor->id}/{$service->id}/book", [
                'date' => $today->format('d-m-Y'),
                'start_time' => '09:00',
            ]);

        $response->assertStatus(422);

        // But 10:00 should be available
        $response = $this->actingAs($patient, 'sanctum')
            ->postJson("/api/v1/booking/{$doctor->id}/{$service->id}/book", [
                'date' => $today->format('d-m-Y'),
                'start_time' => '10:00',
            ]);

        $response->assertOk();
    }

    /**
     * Test full day block
     */
    public function test_full_day_block_prevents_all_bookings(): void
    {
        ['doctor' => $doctor, 'service' => $service] = $this->createDoctorWithService();
        $patient = $this->createPatient();

        $today = Carbon::now();
        $this->addWeeklyAvailabilityForDoctor($doctor, $today, [$today->dayOfWeek], '09:00', '17:00');
        
        // Block full day
        $this->blockDoctorDuring($doctor, $today, '00:00', '23:59');

        // Try to book at any time
        $response = $this->actingAs($patient, 'sanctum')
            ->postJson("/api/v1/booking/{$doctor->id}/{$service->id}/book", [
                'date' => $today->format('d-m-Y'),
                'start_time' => '10:00',
            ]);

        $response->assertStatus(422);
    }

    // ==================== PERFORMANCE TESTS ====================

    /**
     * Test system handles many appointments efficiently
     */
    public function test_system_handles_many_appointments(): void
    {
        ['doctor' => $doctor, 'service' => $service] = $this->createDoctorWithService();

        $today = Carbon::now();
        // 8 hours = 16 slots of 30 minutes
        $this->addWeeklyAvailabilityForDoctor($doctor, $today, [$today->dayOfWeek], '09:00', '17:00');

        // Book 10 appointments
        for ($i = 0; $i < 10; $i++) {
            $patient = $this->createPatient();
            $hour = 9 + intdiv($i, 2);
            $minute = ($i % 2) * 30;
            $time = sprintf('%02d:%02d', $hour, $minute);

            $this->actingAs($patient, 'sanctum')
                ->postJson("/api/v1/booking/{$doctor->id}/{$service->id}/book", [
                    'date' => $today->format('d-m-Y'),
                    'start_time' => $time,
                ])->assertOk();
        }

        // Verify availability still works
        $response = $this->getJson("/api/v1/appointement/{$doctor->id}/{$service->id}/availability");
        $response->assertOk();
        
        $slots = $response->json('data.next_available_slots');
        $this->assertNotEmpty($slots);
        
        // Should have 6 remaining slots (16 total - 10 booked)
        // But since we're only returning up to 5 slots, just verify we have some
        $this->assertLessThanOrEqual(5, count($slots));
    }
}
