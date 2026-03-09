<?php

use App\Models\Patient;
use App\Models\UrgentBooking;
use Illuminate\Support\Facades\Notification;

// ─── Anonymous submit ─────────────────────────────────────────────────────────

it('anyone can submit an urgent booking anonymously', function () {
    Notification::fake();

    $this->postJson('/api/v1/urgent-booking/submit', [
        'patient_name'  => 'Jane Doe',
        'patient_phone' => '0600000001',
        'patient_email' => 'jane@example.com',
        'reason'        => 'Urgent help needed right now.',
    ])->assertCreated()
        ->assertJsonPath('success', true);

    $this->assertDatabaseHas('urgent_bookings', ['patient_email' => 'jane@example.com']);
});

// ─── Authenticated submit ─────────────────────────────────────────────────────

it('authenticated patient urgent booking links patient id', function () {
    Notification::fake();
    $patient = Patient::factory()->verified()->create();

    $this->actAsPatient($patient)
        ->postJson('/api/v1/urgent-booking/submit', [
            'patient_name'  => $patient->first_name . ' ' . $patient->last_name,
            'patient_phone' => '0600000002',
            'patient_email' => $patient->email,
            'reason'        => 'Authenticated urgent booking request.',
        ])->assertCreated()
        ->assertJsonPath('success', true);

    $this->assertDatabaseHas('urgent_bookings', [
        'patient_email' => $patient->email,
        'patient_id'    => $patient->id,
    ]);
});

// ─── Validation ───────────────────────────────────────────────────────────────

it('urgent booking requires patient_name, patient_phone and reason', function () {
    $this->postJson('/api/v1/urgent-booking/submit', [])
        ->assertStatus(422);
});

it('urgent booking requires a valid email when email provided', function () {
    $this->postJson('/api/v1/urgent-booking/submit', [
        'patient_name'  => 'Test User',
        'patient_phone' => '0600000003',
        'patient_email' => 'not-an-email',
        'reason'        => 'Some valid reason here.',
    ])->assertStatus(422);
});

// ─── My bookings ──────────────────────────────────────────────────────────────

it('patient can list their own urgent bookings', function () {
    $patient = Patient::factory()->verified()->create();
    $other   = Patient::factory()->verified()->create();

    UrgentBooking::factory()->forPatient($patient)->count(2)->create();
    UrgentBooking::factory()->forPatient($other)->count(3)->create();

    $response = $this->actAsPatient($patient)
        ->getJson('/api/v1/urgent-booking/my-bookings');

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(2);
});

it('my bookings requires authentication', function () {
    $this->getJson('/api/v1/urgent-booking/my-bookings')->assertStatus(401);
});

// ─── Show ─────────────────────────────────────────────────────────────────────

it('patient can view their own urgent booking', function () {
    $patient = Patient::factory()->verified()->create();
    $booking = UrgentBooking::factory()->forPatient($patient)->create();

    $this->actAsPatient($patient)
        ->getJson("/api/v1/urgent-booking/{$booking->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $booking->id);
});

it('patient cannot view another patient urgent booking', function () {
    $patient = Patient::factory()->verified()->create();
    $other   = Patient::factory()->verified()->create();
    $booking = UrgentBooking::factory()->forPatient($other)->create();

    $this->actAsPatient($patient)
        ->getJson("/api/v1/urgent-booking/{$booking->id}")
        ->assertStatus(404);
});

it('show urgent booking requires authentication', function () {
    $booking = UrgentBooking::factory()->create();

    $this->getJson("/api/v1/urgent-booking/{$booking->id}")
        ->assertStatus(401);
});

// ─── Multilang ────────────────────────────────────────────────────────────────

it('urgent booking submit response message has all 3 locales', function () {
    Notification::fake();

    $response = $this->postJson('/api/v1/urgent-booking/submit', [
        'patient_name'  => 'Multilang Test',
        'patient_phone' => '0600000099',
        'patient_email' => 'multilang@example.com',
        'reason'        => 'Checking multilang messages in response.',
    ])->assertCreated();

    expect($response->json('message'))->toBeMultilang();
});
