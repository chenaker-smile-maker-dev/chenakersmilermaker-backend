<?php

use App\Enums\UrgentBookingStatus;
use App\Models\UrgentBooking;
use Tests\Browser\Core\FilamentPage;

// ─── List ─────────────────────────────────────────────────────────────────────

it('urgent bookings list page loads', function () {
    $page = adminVisit(FilamentPage::urgentBookings());

    $page->assertPathIs(FilamentPage::urgentBookings());
});

it('urgent bookings list shows table rows', function () {
    UrgentBooking::factory()->count(2)->create();

    $page = adminVisit(FilamentPage::urgentBookings());

    $page->assertPresent('.fi-ta-row');
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

// ─── View page ────────────────────────────────────────────────────────────────

it('urgent booking view page loads', function () {
    $booking = UrgentBooking::factory()->create();

    $page = adminVisit(FilamentPage::urgentBooking($booking->id));

    $page->assertPresent('.fi-main');
});

it('urgent booking view shows reason text', function () {
    $booking = UrgentBooking::factory()->create([
        'reason' => 'BrowserVisibleReason for urgent booking test.',
    ]);

    $page = adminVisit(FilamentPage::urgentBooking($booking->id));

    $page->assertSee('BrowserVisibleReason');
});

it('urgent booking view shows accept and reject actions for pending status', function () {
    $booking = UrgentBooking::factory()->pending()->create();

    $page = adminVisit(FilamentPage::urgentBooking($booking->id));

    $page->assertSee('Accept')
        ->assertSee('Reject');
});

it('urgent booking view shows complete action for accepted status', function () {
    $booking = UrgentBooking::factory()->accepted()->create();

    $page = adminVisit(FilamentPage::urgentBooking($booking->id));

    $page->assertSee('Mark Complete');
});
