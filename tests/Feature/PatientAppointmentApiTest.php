<?php

use App\Enums\Appointment\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Service;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;

// ─── Helpers ──────────────────────────────────────────────────────────────────

function makeAppointment(Patient $patient, array $attrs = []): Appointment
{
    $doctor  = Doctor::factory()->create();
    $service = Service::factory()->create(['duration' => 60]);

    return Appointment::factory()->create(array_merge([
        'patient_id' => $patient->id,
        'doctor_id'  => $doctor->id,
        'service_id' => $service->id,
        'status'     => AppointmentStatus::PENDING,
        'from'       => now()->addDay(),
        'to'         => now()->addDay()->addHour(),
    ], $attrs));
}

beforeEach(fn () => Carbon::setTestNow(Carbon::create(2026, 6, 15, 10, 0)));
afterEach(fn ()  => Carbon::setTestNow());

// ─── List ─────────────────────────────────────────────────────────────────────

it('patient can list their appointments', function () {
    $patient = Patient::factory()->verified()->create();
    makeAppointment($patient);
    makeAppointment($patient);

    $other = Patient::factory()->verified()->create();
    makeAppointment($other);

    $response = $this->actAsPatient($patient)
        ->getJson('/api/v1/patient/appointments/');

    $response->assertOk()->assertJsonPath('success', true);
    expect($response->json('data.pagination.total'))->toBe(2);
});

it('can filter appointments by status', function () {
    $patient = Patient::factory()->verified()->create();
    makeAppointment($patient, ['status' => AppointmentStatus::PENDING]);
    makeAppointment($patient, ['status' => AppointmentStatus::CONFIRMED]);
    makeAppointment($patient, ['status' => AppointmentStatus::COMPLETED]);

    $response = $this->actAsPatient($patient)
        ->getJson('/api/v1/patient/appointments/?status=pending');

    $response->assertOk();
    expect($response->json('data.pagination.total'))->toBe(1);
});

it('list appointments requires authentication', function () {
    $this->getJson('/api/v1/patient/appointments/')->assertStatus(401);
});

// ─── Show ─────────────────────────────────────────────────────────────────────

it('patient can view their appointment', function () {
    $patient     = Patient::factory()->verified()->create();
    $appointment = makeAppointment($patient);

    $this->actAsPatient($patient)
        ->getJson("/api/v1/patient/appointments/{$appointment->id}")
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.id', $appointment->id);
});

it('patient cannot view another patients appointment', function () {
    $patient     = Patient::factory()->verified()->create();
    $other       = Patient::factory()->verified()->create();
    $appointment = makeAppointment($other);

    $response = $this->actAsPatient($patient)
        ->getJson("/api/v1/patient/appointments/{$appointment->id}");

    expect($response->status())->toBeIn([403, 422]);
    expect($response->json('success'))->toBeFalse();
});

// ─── Cancel ───────────────────────────────────────────────────────────────────

it('patient can request appointment cancellation', function () {
    Notification::fake();
    $patient     = Patient::factory()->verified()->create();
    $appointment = makeAppointment($patient, ['status' => AppointmentStatus::PENDING]);

    $this->actAsPatient($patient)
        ->postJson("/api/v1/patient/appointments/{$appointment->id}/cancel", [
            'reason' => 'I have a scheduling conflict with another appointment.',
        ])
        ->assertOk()
        ->assertJsonPath('success', true);

    $this->assertDatabaseHas('appointments', [
        'id'                    => $appointment->id,
        'change_request_status' => 'pending_cancellation',
    ]);
});

it('cancellation requires a reason', function () {
    $patient     = Patient::factory()->verified()->create();
    $appointment = makeAppointment($patient);

    $this->actAsPatient($patient)
        ->postJson("/api/v1/patient/appointments/{$appointment->id}/cancel", [])
        ->assertStatus(422);
});

it('cannot cancel a completed appointment', function () {
    $patient     = Patient::factory()->verified()->create();
    $appointment = makeAppointment($patient, ['status' => AppointmentStatus::COMPLETED]);

    $this->actAsPatient($patient)
        ->postJson("/api/v1/patient/appointments/{$appointment->id}/cancel", [
            'reason' => 'I no longer need this appointment to be completed.',
        ])
        ->assertStatus(422);
});

it('cannot cancel a rejected appointment', function () {
    $patient     = Patient::factory()->verified()->create();
    $appointment = makeAppointment($patient, ['status' => AppointmentStatus::REJECTED]);

    $this->actAsPatient($patient)
        ->postJson("/api/v1/patient/appointments/{$appointment->id}/cancel", [
            'reason' => 'I would like to cancel this rejected appointment.',
        ])
        ->assertStatus(422);
});

it('cancel response message has all 3 locales', function () {
    Notification::fake();
    $patient     = Patient::factory()->verified()->create();
    $appointment = makeAppointment($patient, ['status' => AppointmentStatus::PENDING]);

    $response = $this->actAsPatient($patient)
        ->postJson("/api/v1/patient/appointments/{$appointment->id}/cancel", [
            'reason' => 'Verifying the multilang message on cancellation.',
        ])->assertOk();

    expect($response->json('message'))->toBeMultilang();
});

// ─── Reschedule ───────────────────────────────────────────────────────────────

it('patient can request reschedule', function () {
    Notification::fake();
    $patient     = Patient::factory()->verified()->create();
    $appointment = makeAppointment($patient, ['status' => AppointmentStatus::CONFIRMED]);

    $this->actAsPatient($patient)
        ->postJson("/api/v1/patient/appointments/{$appointment->id}/reschedule", [
            'reason'         => 'I need to change to a more convenient time slot.',
            'new_date'       => now()->addDays(5)->format('d-m-Y'),
            'new_start_time' => '10:00',
        ])
        ->assertOk()
        ->assertJsonPath('success', true);

    $this->assertDatabaseHas('appointments', [
        'id'                    => $appointment->id,
        'change_request_status' => 'pending_reschedule',
    ]);
});

it('reschedule validates date format', function () {
    $patient     = Patient::factory()->verified()->create();
    $appointment = makeAppointment($patient);

    $this->actAsPatient($patient)
        ->postJson("/api/v1/patient/appointments/{$appointment->id}/reschedule", [
            'reason'         => 'I need to change the time for this appointment.',
            'new_date'       => '2026-06-20',  // wrong format – should be d-m-Y
            'new_start_time' => '10:00',
        ])
        ->assertStatus(422);
});

it('cannot reschedule if pending change already exists', function () {
    Notification::fake();
    $patient     = Patient::factory()->verified()->create();
    $appointment = makeAppointment($patient, [
        'status'                => AppointmentStatus::PENDING,
        'change_request_status' => 'pending_reschedule',
    ]);

    $this->actAsPatient($patient)
        ->postJson("/api/v1/patient/appointments/{$appointment->id}/reschedule", [
            'reason'         => 'Trying to reschedule again while one is pending.',
            'new_date'       => now()->addDays(5)->format('d-m-Y'),
            'new_start_time' => '14:00',
        ])
        ->assertStatus(422);
});
