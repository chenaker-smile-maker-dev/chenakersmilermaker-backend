<?php

use App\Enums\Appointment\AppointmentStatus;
use App\Enums\Appointment\ChangeRequestStatus;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Service;
use Tests\Browser\Core\FilamentPage;

// ─── Helpers ──────────────────────────────────────────────────────────────────

function makeBrowserAppointment(array $attrs = []): Appointment
{
    $doctor  = Doctor::factory()->create();
    $service = Service::factory()->create(['duration' => 60]);
    $patient = Patient::factory()->verified()->create();

    return Appointment::factory()->create(array_merge([
        'patient_id' => $patient->id,
        'doctor_id'  => $doctor->id,
        'service_id' => $service->id,
        'status'     => AppointmentStatus::PENDING,
        'from'       => now()->addDay()->setHour(10)->setMinute(0),
        'to'         => now()->addDay()->setHour(11)->setMinute(0),
    ], $attrs));
}

// ─── List ─────────────────────────────────────────────────────────────────────

it('appointments list page loads', function () {
    $page = adminVisit(FilamentPage::appointments());

    $page->assertPathIs(FilamentPage::appointments());
});

it('appointments list shows table with appointments', function () {
    makeBrowserAppointment();

    $page = adminVisit(FilamentPage::appointments());

    $page->assertPresent('.fi-ta-row');
});

it('appointments list shows status badges', function () {
    makeBrowserAppointment(['status' => AppointmentStatus::PENDING]);
    makeBrowserAppointment(['status' => AppointmentStatus::CONFIRMED]);

    $page = adminVisit(FilamentPage::appointments());

    $page->assertSee('Pending')
        ->assertSee('Confirmed');
});

// ─── View page ────────────────────────────────────────────────────────────────

it('appointment view page loads', function () {
    $appointment = makeBrowserAppointment();

    $page = adminVisit(FilamentPage::appointment($appointment->id));

    $page->assertPresent('.fi-main');
});

it('appointment view shows patient name', function () {
    $patient = Patient::factory()->create([
        'first_name' => 'ApptViewPatient',
        'last_name'  => 'Browser',
    ]);
    $doctor      = Doctor::factory()->create();
    $service     = Service::factory()->create();
    $appointment = Appointment::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id'  => $doctor->id,
        'service_id' => $service->id,
    ]);

    $page = adminVisit(FilamentPage::appointment($appointment->id));

    $page->assertSee('ApptViewPatient');
});

it('appointment view shows confirm and reject action buttons for pending status', function () {
    $appointment = makeBrowserAppointment(['status' => AppointmentStatus::PENDING]);

    $page = adminVisit(FilamentPage::appointment($appointment->id));

    $page->assertSee('Confirm')
        ->assertSee('Reject');
});

it('appointment with pending cancellation shows approve/reject cancellation buttons', function () {
    $appointment = makeBrowserAppointment([
        'status'                => AppointmentStatus::CONFIRMED,
        'change_request_status' => ChangeRequestStatus::PENDING_CANCELLATION,
        'cancellation_reason'   => 'Patient cannot attend.',
    ]);

    $page = adminVisit(FilamentPage::appointment($appointment->id));

    $page->assertSee('Approve Cancellation')
        ->assertSee('Reject Cancellation');
});

it('appointment with pending reschedule shows approve/reject reschedule buttons', function () {
    $appointment = makeBrowserAppointment([
        'status'                => AppointmentStatus::CONFIRMED,
        'change_request_status' => ChangeRequestStatus::PENDING_RESCHEDULE,
        'reschedule_reason'     => 'Patient requests earlier slot.',
        'requested_new_from'    => now()->addDays(2)->setHour(9),
        'requested_new_to'      => now()->addDays(2)->setHour(10),
    ]);

    $page = adminVisit(FilamentPage::appointment($appointment->id));

    $page->assertSee('Approve Reschedule')
        ->assertSee('Reject Reschedule');
});
