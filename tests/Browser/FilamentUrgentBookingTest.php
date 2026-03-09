<?php

use App\Enums\UrgentBookingStatus;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\UrgentBooking;
use Tests\Browser\Core\BrowserAssertions;
use Tests\Browser\Core\FilamentPage;

// ─── List ─────────────────────────────────────────────────────────────────────

it('urgent bookings list page loads', function () {
    $page = adminVisit(FilamentPage::urgentBookings());

    $page->assertPathIs(FilamentPage::urgentBookings());
});

it('urgent bookings list shows table rows', function () {
    UrgentBooking::factory()->count(2)->create();

    $page = adminVisit(FilamentPage::urgentBookings());

    BrowserAssertions::assertTableHasRows($page);
});

it('urgent bookings list shows status badges', function () {
    UrgentBooking::factory()->pending()->create();
    UrgentBooking::factory()->accepted()->create();

    $page = adminVisit(FilamentPage::urgentBookings());

    $page->assertSee('Pending')
        ->assertSee('Accepted');
});

it('urgent bookings list shows patient name column', function () {
    UrgentBooking::factory()->create(['patient_name' => 'BrowserUrgentPatient']);

    $page = adminVisit(FilamentPage::urgentBookings());

    $page->assertSee('BrowserUrgentPatient');
});

// ─── Search ───────────────────────────────────────────────────────────────────

it('can search urgent bookings by patient name', function () {
    UrgentBooking::factory()->create(['patient_name' => 'SearchableUrgent Patient']);
    UrgentBooking::factory()->create(['patient_name' => 'AnotherName Here']);

    $page = adminVisit(FilamentPage::urgentBookings());

    $page->type('input[placeholder*="Search"]', 'SearchableUrgent')
        ->assertSee('SearchableUrgent Patient');
});

// ─── View ─────────────────────────────────────────────────────────────────────

it('urgent booking view page loads', function () {
    $booking = UrgentBooking::factory()->create();

    $page = adminVisit(FilamentPage::urgentBooking($booking->id));

    $page->assertPresent('main');
});

it('urgent booking view shows reason text', function () {
    $booking = UrgentBooking::factory()->create([
        'reason' => 'BrowserVisibleReason for urgent booking test.',
    ]);

    $page = adminVisit(FilamentPage::urgentBooking($booking->id));

    $page->assertSee('BrowserVisibleReason');
});

// ─── Actions ─────────────────────────────────────────────────────────────────

it('can accept a pending urgent booking', function () {
    $doctor  = Doctor::factory()->create();
    $booking = UrgentBooking::factory()->pending()->create();

    $page = adminVisit(FilamentPage::urgentBooking($booking->id));

    $page->assertSee('Accept')
        ->press('Accept')
        ->waitFor('[role="dialog"]')
        ->select('[data-identifier="assigned_doctor_id"]', (string) $doctor->id)
        ->press('Confirm');

    expect($booking->fresh()->status)->toBe(UrgentBookingStatus::ACCEPTED);
});

it('can reject a pending urgent booking', function () {
    $booking = UrgentBooking::factory()->pending()->create();

    $page = adminVisit(FilamentPage::urgentBooking($booking->id));

    $page->press('Reject')
        ->waitFor('[role="dialog"]')
        ->type('textarea', 'No available slots at this time.')
        ->press('Confirm');

    expect($booking->fresh()->status)->toBe(UrgentBookingStatus::REJECTED);
});

it('can complete an accepted urgent booking', function () {
    $booking = UrgentBooking::factory()->accepted()->create();

    $page = adminVisit(FilamentPage::urgentBooking($booking->id));

    $page->press('Complete')
        ->waitFor('[role="dialog"]')
        ->press('Confirm');

    expect($booking->fresh()->status)->toBe(UrgentBookingStatus::COMPLETED);
});
