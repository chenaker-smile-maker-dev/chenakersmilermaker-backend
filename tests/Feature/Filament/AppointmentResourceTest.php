<?php

use App\Enums\Appointment\AppointmentStatus;
use App\Filament\Admin\Resources\Appointments\Pages\EditAppointment;
use App\Filament\Admin\Resources\Appointments\Pages\ListAppointments;
use App\Filament\Admin\Resources\Appointments\Pages\ViewAppointment;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Service;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    actingAs(User::factory()->create());
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

// ─── List ──────────────────────────────────────────────────────────────────────

it('can render the appointments list page', function () {
    livewire(ListAppointments::class)->assertOk();
});

it('shows appointment records in the table', function () {
    $doctor   = Doctor::factory()->create();
    $service  = Service::factory()->create();
    $patient  = Patient::factory()->create();
    $appts    = Appointment::factory()->count(3)->create([
        'doctor_id'  => $doctor->id,
        'service_id' => $service->id,
        'patient_id' => $patient->id,
        'status'     => AppointmentStatus::PENDING,
    ]);

    livewire(ListAppointments::class)
        ->assertOk()
        ->assertCanSeeTableRecords($appts);
});

it('can filter appointments by status', function () {
    $doctor  = Doctor::factory()->create();
    $service = Service::factory()->create();
    $patient = Patient::factory()->create();

    $pending   = Appointment::factory()->count(2)->create([
        'doctor_id' => $doctor->id,
        'service_id' => $service->id,
        'patient_id' => $patient->id,
        'status'    => AppointmentStatus::PENDING,
    ]);
    $completed = Appointment::factory()->count(2)->create([
        'doctor_id' => $doctor->id,
        'service_id' => $service->id,
        'patient_id' => $patient->id,
        'status'    => AppointmentStatus::COMPLETED,
    ]);

    livewire(ListAppointments::class)
        ->filterTable('status', AppointmentStatus::PENDING->value)
        ->assertCanSeeTableRecords($pending)
        ->assertCanNotSeeTableRecords($completed);
});

it('has the expected table columns', function () {
    livewire(ListAppointments::class)
        ->assertTableColumnExists('patient.full_name')
        ->assertTableColumnExists('doctor.display_name')
        ->assertTableColumnExists('status');
});

// ─── View ──────────────────────────────────────────────────────────────────────

it('can render the view appointment page', function () {
    $appt = Appointment::factory()->create();

    livewire(ViewAppointment::class, ['record' => $appt->id])
        ->assertOk();
});

it('can confirm a pending appointment via header action', function () {
    $appt = Appointment::factory()->create(['status' => AppointmentStatus::PENDING]);

    livewire(ViewAppointment::class, ['record' => $appt->id])
        ->callAction('confirm')
        ->assertNotified();

    assertDatabaseHas(Appointment::class, [
        'id'     => $appt->id,
        'status' => AppointmentStatus::CONFIRMED->value,
    ]);
});

it('can reject a pending appointment with a reason via header action', function () {
    $appt = Appointment::factory()->create(['status' => AppointmentStatus::PENDING]);

    livewire(ViewAppointment::class, ['record' => $appt->id])
        ->callAction('reject', data: ['admin_notes' => 'Patient did not show'])
        ->assertNotified();

    assertDatabaseHas(Appointment::class, [
        'id'     => $appt->id,
        'status' => AppointmentStatus::REJECTED->value,
    ]);
});

it('reject action requires admin notes', function () {
    $appt = Appointment::factory()->create(['status' => AppointmentStatus::PENDING]);

    livewire(ViewAppointment::class, ['record' => $appt->id])
        ->callAction('reject', data: ['admin_notes' => null])
        ->assertHasActionErrors(['admin_notes' => 'required']);
});

it('can complete a confirmed appointment via header action', function () {
    $appt = Appointment::factory()->create(['status' => AppointmentStatus::CONFIRMED]);

    livewire(ViewAppointment::class, ['record' => $appt->id])
        ->callAction('complete')
        ->assertNotified();

    assertDatabaseHas(Appointment::class, [
        'id'     => $appt->id,
        'status' => AppointmentStatus::COMPLETED->value,
    ]);
});

// ─── Table Row Actions ─────────────────────────────────────────────────────────

it('can confirm a pending appointment from the table row action', function () {
    $doctor  = Doctor::factory()->create();
    $service = Service::factory()->create();
    $patient = Patient::factory()->create();
    $appt    = Appointment::factory()->create([
        'doctor_id' => $doctor->id,
        'service_id' => $service->id,
        'patient_id' => $patient->id,
        'status'    => AppointmentStatus::PENDING,
    ]);

    livewire(ListAppointments::class)
        ->callAction(TestAction::make('confirm')->table($appt))
        ->assertNotified();

    assertDatabaseHas(Appointment::class, [
        'id'     => $appt->id,
        'status' => AppointmentStatus::CONFIRMED->value,
    ]);
});

it('can reject a pending appointment from the table row action', function () {
    $doctor  = Doctor::factory()->create();
    $service = Service::factory()->create();
    $patient = Patient::factory()->create();
    $appt    = Appointment::factory()->create([
        'doctor_id' => $doctor->id,
        'service_id' => $service->id,
        'patient_id' => $patient->id,
        'status'    => AppointmentStatus::PENDING,
    ]);

    livewire(ListAppointments::class)
        ->callAction(TestAction::make('reject')->table($appt), data: ['admin_notes' => 'Cancelled by admin'])
        ->assertNotified();

    assertDatabaseHas(Appointment::class, [
        'id'     => $appt->id,
        'status' => AppointmentStatus::REJECTED->value,
    ]);
});

it('can complete a confirmed appointment from the table row action', function () {
    $doctor  = Doctor::factory()->create();
    $service = Service::factory()->create();
    $patient = Patient::factory()->create();
    $appt    = Appointment::factory()->create([
        'doctor_id' => $doctor->id,
        'service_id' => $service->id,
        'patient_id' => $patient->id,
        'status'    => AppointmentStatus::CONFIRMED,
    ]);

    livewire(ListAppointments::class)
        ->callAction(TestAction::make('complete')->table($appt))
        ->assertNotified();

    assertDatabaseHas(Appointment::class, [
        'id'     => $appt->id,
        'status' => AppointmentStatus::COMPLETED->value,
    ]);
});

// ─── Edit ──────────────────────────────────────────────────────────────────────

it('can render the edit appointment page', function () {
    $appt = Appointment::factory()->create();

    livewire(EditAppointment::class, ['record' => $appt->id])
        ->assertOk();
});
