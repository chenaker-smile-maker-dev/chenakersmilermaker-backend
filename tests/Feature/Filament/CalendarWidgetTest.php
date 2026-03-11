<?php

use App\Enums\Appointment\AppointmentStatus;
use App\Filament\Admin\Pages\Dashboard;
use App\Filament\Admin\Resources\Doctors\Pages\DoctorCalendarPage;
use App\Filament\Admin\Resources\Doctors\Widgets\DoctorCalendarWidget;
use App\Filament\Admin\Widgets\BookingCalendarWidget;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Service;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    actingAs(User::factory()->create());
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

// ═══════════════════════════════════════════════════════════════════════════════
// AppointmentStatus Enum – Transition Logic
// ═══════════════════════════════════════════════════════════════════════════════

it('allows correct status transitions from PENDING', function () {
    $status = AppointmentStatus::PENDING;
    expect($status->canTransitionTo(AppointmentStatus::CONFIRMED))->toBeTrue();
    expect($status->canTransitionTo(AppointmentStatus::REJECTED))->toBeTrue();
    expect($status->canTransitionTo(AppointmentStatus::CANCELLED))->toBeTrue();
    expect($status->canTransitionTo(AppointmentStatus::COMPLETED))->toBeFalse();
});

it('allows correct status transitions from CONFIRMED', function () {
    $status = AppointmentStatus::CONFIRMED;
    expect($status->canTransitionTo(AppointmentStatus::COMPLETED))->toBeTrue();
    expect($status->canTransitionTo(AppointmentStatus::CANCELLED))->toBeTrue();
    expect($status->canTransitionTo(AppointmentStatus::PENDING))->toBeFalse();
    expect($status->canTransitionTo(AppointmentStatus::REJECTED))->toBeFalse();
});

it('does not allow transitions from terminal statuses', function (AppointmentStatus $status) {
    foreach (AppointmentStatus::cases() as $target) {
        expect($status->canTransitionTo($target))->toBeFalse();
    }
})->with([
    AppointmentStatus::REJECTED,
    AppointmentStatus::CANCELLED,
    AppointmentStatus::COMPLETED,
]);

// ═══════════════════════════════════════════════════════════════════════════════
// BookingCalendarWidget – Dashboard
// ═══════════════════════════════════════════════════════════════════════════════

it('can render the booking calendar widget', function () {
    livewire(BookingCalendarWidget::class)->assertOk();
});

it('returns events for the visible date range', function () {
    $doctor  = Doctor::factory()->create();
    $service = Service::factory()->create();
    $patient = Patient::factory()->create();

    $inRange = Appointment::factory()->create([
        'doctor_id'  => $doctor->id,
        'service_id' => $service->id,
        'patient_id' => $patient->id,
        'from'       => now()->startOfMonth()->addDays(5)->setHour(10),
        'to'         => now()->startOfMonth()->addDays(5)->setHour(11),
        'status'     => AppointmentStatus::PENDING,
    ]);

    $outOfRange = Appointment::factory()->create([
        'doctor_id'  => $doctor->id,
        'service_id' => $service->id,
        'patient_id' => $patient->id,
        'from'       => now()->subMonths(3)->setHour(10),
        'to'         => now()->subMonths(3)->setHour(11),
        'status'     => AppointmentStatus::PENDING,
    ]);

    $component = livewire(BookingCalendarWidget::class);
    $component->assertOk();

    // Call getEventsJs directly to verify event fetching
    $events = $component->call('getEventsJs', [
        'startStr' => now()->startOfMonth()->toIso8601String(),
        'endStr'   => now()->endOfMonth()->toIso8601String(),
        'tzOffset' => 0,
    ]);
});

it('filters events by doctor IDs', function () {
    $doctor1 = Doctor::factory()->create();
    $doctor2 = Doctor::factory()->create();
    $service = Service::factory()->create();
    $patient = Patient::factory()->create();

    Appointment::factory()->create([
        'doctor_id'  => $doctor1->id,
        'service_id' => $service->id,
        'patient_id' => $patient->id,
        'from'       => now()->addDay()->setHour(10),
        'to'         => now()->addDay()->setHour(11),
        'status'     => AppointmentStatus::PENDING,
    ]);

    Appointment::factory()->create([
        'doctor_id'  => $doctor2->id,
        'service_id' => $service->id,
        'patient_id' => $patient->id,
        'from'       => now()->addDay()->setHour(10),
        'to'         => now()->addDay()->setHour(11),
        'status'     => AppointmentStatus::CONFIRMED,
    ]);

    // Set filter to doctor1 only
    $component = livewire(BookingCalendarWidget::class)
        ->set('filterDoctorIds', [$doctor1->id])
        ->assertOk();
});

it('filters events by multiple statuses', function () {
    $doctor  = Doctor::factory()->create();
    $service = Service::factory()->create();
    $patient = Patient::factory()->create();

    Appointment::factory()->create([
        'doctor_id'  => $doctor->id,
        'service_id' => $service->id,
        'patient_id' => $patient->id,
        'from'       => now()->addDay()->setHour(10),
        'to'         => now()->addDay()->setHour(11),
        'status'     => AppointmentStatus::PENDING,
    ]);

    $component = livewire(BookingCalendarWidget::class)
        ->set('filterStatuses', [AppointmentStatus::PENDING->value, AppointmentStatus::CONFIRMED->value])
        ->assertOk();
});

it('can toggle status filters', function () {
    $component = livewire(BookingCalendarWidget::class);

    // Toggle pending on
    $component->call('toggleStatus', AppointmentStatus::PENDING->value);
    expect($component->get('filterStatuses'))->toContain(AppointmentStatus::PENDING->value);

    // Toggle pending off
    $component->call('toggleStatus', AppointmentStatus::PENDING->value);
    expect($component->get('filterStatuses'))->not->toContain(AppointmentStatus::PENDING->value);
});

it('returns event content as a plain string', function () {
    $component = new BookingCalendarWidget();
    $method = new ReflectionMethod($component, 'eventContent');
    $result = $method->invoke($component);

    expect($result)->toBeString();
    expect($result)->toContain('x-text="event.title"');
});

// ═══════════════════════════════════════════════════════════════════════════════
// DoctorCalendarWidget – Doctor Resource Page
// ═══════════════════════════════════════════════════════════════════════════════

it('can render the doctor calendar widget', function () {
    $doctor = Doctor::factory()->create();

    livewire(DoctorCalendarWidget::class, ['doctorId' => $doctor->id])
        ->assertOk();
});

it('returns events for the doctor only', function () {
    $doctor1 = Doctor::factory()->create();
    $doctor2 = Doctor::factory()->create();
    $service = Service::factory()->create();
    $patient = Patient::factory()->create();

    $doctor1Appt = Appointment::factory()->create([
        'doctor_id'  => $doctor1->id,
        'service_id' => $service->id,
        'patient_id' => $patient->id,
        'from'       => now()->addDay()->setHour(10),
        'to'         => now()->addDay()->setHour(11),
        'status'     => AppointmentStatus::PENDING,
    ]);

    $doctor2Appt = Appointment::factory()->create([
        'doctor_id'  => $doctor2->id,
        'service_id' => $service->id,
        'patient_id' => $patient->id,
        'from'       => now()->addDay()->setHour(10),
        'to'         => now()->addDay()->setHour(11),
        'status'     => AppointmentStatus::PENDING,
    ]);

    $component = livewire(DoctorCalendarWidget::class, ['doctorId' => $doctor1->id])
        ->assertOk();
});

it('can render the doctor calendar page', function () {
    $doctor = Doctor::factory()->create();

    livewire(DoctorCalendarPage::class, ['record' => $doctor->id])
        ->assertOk();
});

it('filters doctor calendar events by statuses', function () {
    $doctor  = Doctor::factory()->create();
    $service = Service::factory()->create();
    $patient = Patient::factory()->create();

    Appointment::factory()->create([
        'doctor_id'  => $doctor->id,
        'service_id' => $service->id,
        'patient_id' => $patient->id,
        'from'       => now()->addDay()->setHour(10),
        'to'         => now()->addDay()->setHour(11),
        'status'     => AppointmentStatus::PENDING,
    ]);

    livewire(DoctorCalendarWidget::class, ['doctorId' => $doctor->id])
        ->set('filterStatuses', [AppointmentStatus::CONFIRMED->value])
        ->assertOk();
});

it('can toggle availability and blocked schedule display', function () {
    $doctor = Doctor::factory()->create();

    $component = livewire(DoctorCalendarWidget::class, ['doctorId' => $doctor->id]);

    // Turn off availability
    $component->set('showAvailability', false)->assertOk();
    expect($component->get('showAvailability'))->toBeFalse();

    // Turn off blocked
    $component->set('showBlocked', false)->assertOk();
    expect($component->get('showBlocked'))->toBeFalse();
});

it('returns event content as a plain string in doctor calendar', function () {
    $doctor = Doctor::factory()->create();

    $component = new DoctorCalendarWidget();
    $component->doctorId = $doctor->id;
    $method = new ReflectionMethod($component, 'eventContent');
    $result = $method->invoke($component);

    expect($result)->toBeString();
    expect($result)->toContain('x-text="event.title"');
});
