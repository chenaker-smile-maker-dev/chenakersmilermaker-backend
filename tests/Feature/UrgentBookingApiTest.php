<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\UrgentBooking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class UrgentBookingApiTest extends TestCase
{
    use RefreshDatabase;

    // ─── Submit ───────────────────────────────────────────────────────────────

    public function test_anyone_can_submit_urgent_booking(): void
    {
        Notification::fake();

        $response = $this->postJson('/api/v1/urgent-booking/submit', [
            'patient_name'  => 'Sara Mansour',
            'patient_phone' => '0661234567',
            'reason'        => 'Severe chest pain since this morning and I am worried.',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['id', 'patient_name', 'status']]);

        $this->assertDatabaseHas('urgent_bookings', [
            'patient_name'  => 'Sara Mansour',
            'patient_phone' => '0661234567',
        ]);
    }

    public function test_authenticated_patient_urgent_booking_links_patient_id(): void
    {
        Notification::fake();
        $patient = Patient::factory()->verified()->create();

        $response = $this->actAsPatient($patient)
            ->postJson('/api/v1/urgent-booking/submit', [
                'patient_name'  => $patient->full_name,
                'patient_phone' => $patient->phone,
                'reason'        => 'Severe abdominal pain requiring immediate attention.',
            ]);

        $response->assertCreated();

        $this->assertDatabaseHas('urgent_bookings', [
            'patient_id'    => $patient->id,
            'patient_phone' => $patient->phone,
        ]);
    }

    public function test_submit_requires_patient_name_and_phone_and_reason(): void
    {
        $response = $this->postJson('/api/v1/urgent-booking/submit', []);

        $response->assertStatus(422);
    }

    public function test_submit_reason_must_be_at_least_10_characters(): void
    {
        $response = $this->postJson('/api/v1/urgent-booking/submit', [
            'patient_name'  => 'Test Patient',
            'patient_phone' => '0661234567',
            'reason'        => 'Short',
        ]);

        $response->assertStatus(422);
    }

    // ─── My bookings ──────────────────────────────────────────────────────────

    public function test_authenticated_patient_can_list_their_urgent_bookings(): void
    {
        $patient = Patient::factory()->create();
        UrgentBooking::factory()->count(2)->create(['patient_id' => $patient->id]);
        UrgentBooking::factory()->count(3)->create(); // other patients

        $response = $this->actAsPatient($patient)
            ->getJson('/api/v1/urgent-booking/my-bookings');

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertCount(2, $response->json('data'));
    }

    public function test_my_bookings_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/urgent-booking/my-bookings');

        $response->assertStatus(401);
    }

    // ─── Show ─────────────────────────────────────────────────────────────────

    public function test_patient_can_view_own_urgent_booking(): void
    {
        $patient = Patient::factory()->create();
        $booking = UrgentBooking::factory()->create(['patient_id' => $patient->id]);

        $response = $this->actAsPatient($patient)
            ->getJson("/api/v1/urgent-booking/{$booking->id}");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $booking->id)
            ->assertJsonStructure(['data' => ['id', 'reason', 'status', 'patient_name', 'created_at']]);
    }

    public function test_patient_cannot_view_another_patients_booking(): void
    {
        $patient = Patient::factory()->create();
        $other   = Patient::factory()->create();
        $booking = UrgentBooking::factory()->create(['patient_id' => $other->id]);

        $response = $this->actAsPatient($patient)
            ->getJson("/api/v1/urgent-booking/{$booking->id}");

        $response->assertStatus(404);
    }

    public function test_show_urgent_booking_requires_authentication(): void
    {
        $booking = UrgentBooking::factory()->create();

        $response = $this->getJson("/api/v1/urgent-booking/{$booking->id}");

        $response->assertStatus(401);
    }
}
