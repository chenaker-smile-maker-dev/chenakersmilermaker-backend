<?php

use App\Enums\UrgentBookingStatus;
use App\Filament\Admin\Resources\UrgentBookings\Pages\ListUrgentBookings;
use App\Filament\Admin\Resources\UrgentBookings\Pages\ViewUrgentBooking;
use App\Models\Doctor;
use App\Models\UrgentBooking;
use App\Models\User;
use Filament\Facades\Filament;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    actingAs(User::factory()->create());
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

// ─── List ──────────────────────────────────────────────────────────────────────

it('can render the urgent bookings list page', function () {
    livewire(ListUrgentBookings::class)->assertOk();
});

it('shows urgent booking records in the table', function () {
    $bookings = UrgentBooking::factory()->count(3)->pending()->create();

    livewire(ListUrgentBookings::class)
        ->assertOk()
        ->assertCanSeeTableRecords($bookings);
});

it('has the expected table columns', function () {
    livewire(ListUrgentBookings::class)
        ->assertTableColumnExists('patient_name')
        ->assertTableColumnExists('status');
});

// ─── View ──────────────────────────────────────────────────────────────────────

it('can render the view urgent booking page', function () {
    $booking = UrgentBooking::factory()->pending()->create();

    livewire(ViewUrgentBooking::class, ['record' => $booking->id])
        ->assertOk();
});

it('can accept a pending urgent booking', function () {
    $doctor  = Doctor::factory()->create();
    $booking = UrgentBooking::factory()->pending()->create();
    $scheduledAt = now()->addDay()->toDateTimeString();

    livewire(ViewUrgentBooking::class, ['record' => $booking->id])
        ->callAction('accept', data: [
            'scheduled_datetime' => $scheduledAt,
            'assigned_doctor_id' => $doctor->id,
            'admin_notes'        => 'All good.',
        ])
        ->assertNotified();

    assertDatabaseHas(UrgentBooking::class, [
        'id'     => $booking->id,
        'status' => UrgentBookingStatus::ACCEPTED->value,
    ]);
});

it('accept action requires scheduled_datetime', function () {
    $booking = UrgentBooking::factory()->pending()->create();

    livewire(ViewUrgentBooking::class, ['record' => $booking->id])
        ->callAction('accept', data: [
            'scheduled_datetime' => null,
        ])
        ->assertHasActionErrors(['scheduled_datetime' => 'required']);
});

it('can reject a pending urgent booking with a reason', function () {
    $booking = UrgentBooking::factory()->pending()->create();

    livewire(ViewUrgentBooking::class, ['record' => $booking->id])
        ->callAction('reject', data: ['admin_notes' => 'Slot unavailable'])
        ->assertNotified();

    assertDatabaseHas(UrgentBooking::class, [
        'id'     => $booking->id,
        'status' => UrgentBookingStatus::REJECTED->value,
    ]);
});

it('reject action requires admin notes', function () {
    $booking = UrgentBooking::factory()->pending()->create();

    livewire(ViewUrgentBooking::class, ['record' => $booking->id])
        ->callAction('reject', data: ['admin_notes' => null])
        ->assertHasActionErrors(['admin_notes' => 'required']);
});

it('can mark an accepted urgent booking as complete', function () {
    $booking = UrgentBooking::factory()->accepted()->create();

    livewire(ViewUrgentBooking::class, ['record' => $booking->id])
        ->callAction('complete')
        ->assertNotified();

    assertDatabaseHas(UrgentBooking::class, [
        'id'     => $booking->id,
        'status' => UrgentBookingStatus::COMPLETED->value,
    ]);
});

it('does not show the accept/reject actions for an accepted booking', function () {
    $booking = UrgentBooking::factory()->accepted()->create();

    livewire(ViewUrgentBooking::class, ['record' => $booking->id])
        ->assertActionHidden('accept')
        ->assertActionHidden('reject');
});

it('does not show the complete action for a pending booking', function () {
    $booking = UrgentBooking::factory()->pending()->create();

    livewire(ViewUrgentBooking::class, ['record' => $booking->id])
        ->assertActionHidden('complete');
});
