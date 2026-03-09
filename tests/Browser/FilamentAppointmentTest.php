<?php

use App\Enums\Appointment\AppointmentStatus;
use App\Enums\Appointment\ChangeRequestStatus;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Service;
use Tests\Browser\Core\BrowserAssertions;
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

    BrowserAssertions::assertTableHasRows($page);
});

it('appointments list shows status badges', function () {
    makeBrowserAppointment(['status' => AppointmentStatus::PENDING]);
    makeBrowserAppointment(['status' => AppointmentStatus::CONFIRMED]);

    $page = adminVisit(FilamentPage::appointments());

    $page->assertSee('Pending')
        ->assertSee('Confirmed');
});

// ─── Filters ─────────────────────────────────────────────────────────────────

it('can filter appointments by pending status', function () {
    makeBrowserAppointment(['status' => AppointmentStatus::PENDING]);
    makeBrowserAppointment(['status' => AppointmentStatus::COMPLETED]);

    $page = adminVisit(FilamentPage::appointments());

    // Open filters and apply
    $page->click('[data-identifier="open-filters"]')
        ->waitFor('[data-identifier="filter-status"]')
        ->select('[data-identifier="filter-status"]', 'pending')
        ->assertSee('Pending')
        ->assertDontSee('Completed');
});

// ─── View ─────────────────────────────────────────────────────────────────────

it('appointment view page loads', function () {
    $appointment = makeBrowserAppointment();

    $page = adminVisit(FilamentPage::appointment($appointment->id));

    $page->assertPresent('main');
});

it('appointment view shows patient name', function () {
    $patient     = Patient::factory()->create(['first_name' => 'ApptViewPatient', 'last_name' => 'Browser']);
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

// ─── Actions ─────────────────────────────────────────────────────────────────

it('can confirm a pending appointment via action button', function () {
    $appointment = makeBrowserAppointment(['status' => AppointmentStatus::PENDING]);

    $page = adminVisit(FilamentPage::appointment($appointment->id));

    $page->assertSee('Confirm')
        ->press('Confirm')
        ->assertSee('Confirmed');

    expect($appointment->fresh()->status)->toBe(AppointmentStatus::CONFIRMED);
});

it('can reject a pending appointment with a reason', function () {
    $appointment = makeBrowserAppointment(['status' => AppointmentStatus::PENDING]);

    $page = adminVisit(FilamentPage::appointment($appointment->id));

    $page->press('Reject')
        ->waitFor('[role="dialog"]')
        ->type('textarea', 'Slot conflict — please rebook.')
        ->press('Confirm');

    expect($appointment->fresh()->status)->toBe(AppointmentStatus::REJECTED);
});

it('can complete a confirmed appointment', function () {
    $appointment = makeBrowserAppointment(['status' => AppointmentStatus::CONFIRMED]);

    $page = adminVisit(FilamentPage::appointment($appointment->id));

    $page->press('Complete')
        ->waitFor('[role="dialog"]')
        ->press('Confirm');

    expect($appointment->fresh()->status)->toBe(AppointmentStatus::COMPLETED);
});

// ─── Cancellation request handling ───────────────────────────────────────────

it('appointment with pending cancellation shows approve/reject buttons', function () {
    $appointment = makeBrowserAppointment([
        'status'                => AppointmentStatus::CONFIRMED,
        'change_request_status' => ChangeRequestStatus::PENDING_CANCELLATION,
        'cancellation_reason'   => 'Patient cannot attend.',
    ]);

    $page = adminVisit(FilamentPage::appointment($appointment->id));

    $page->assertSee('Approve Cancellation')
        ->assertSee('Reject Cancellation');
});

// ─── Reschedule request handling ─────────────────────────────────────────────

it('appointment with pending reschedule shows approve/reject buttons', function () {
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
