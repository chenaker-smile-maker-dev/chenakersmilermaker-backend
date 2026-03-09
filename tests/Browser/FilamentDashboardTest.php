<?php

use App\Models\User;
use Tests\Browser\Core\AdminSession;
use Tests\Browser\Core\BrowserAssertions;
use Tests\Browser\Core\FilamentPage;

// ─── Dashboard loads ──────────────────────────────────────────────────────────

it('dashboard loads after login', function () {
    $page = adminLogin();

    $page->assertPathIs(FilamentPage::dashboard());
});

it('dashboard shows the navigation sidebar', function () {
    $page = adminLogin();

    BrowserAssertions::assertSidebarPresent($page);
});

it('dashboard page title contains the app name', function () {
    $page = adminLogin();

    $page->assertTitleContains(config('app.name'));
});

// ─── Dashboard widgets ────────────────────────────────────────────────────────

it('dashboard shows stats overview widget heading', function () {
    $page = adminLogin();

    BrowserAssertions::assertWidgetVisible($page, 'Total Patients');
});

it('dashboard shows today appointments section', function () {
    $page = adminLogin();

    // The TodayAppointmentsWidget heading
    $page->assertSee('Today');
});

it('dashboard shows pending actions widget', function () {
    $page = adminLogin();

    $page->assertSee('Pending');
});

// ─── Sidebar navigation ───────────────────────────────────────────────────────

it('can navigate to the patients resource page via sidebar', function () {
    $page = adminLogin();

    $page->clickText('Patients')
        ->assertPathBeginsWith(FilamentPage::patients());
});

it('can navigate to the doctors resource page via sidebar', function () {
    $page = adminLogin();

    $page->clickText('Doctors')
        ->assertPathBeginsWith(FilamentPage::doctors());
});

it('can navigate to the appointments resource page via sidebar', function () {
    $page = adminLogin();

    $page->clickText('Appointments')
        ->assertPathBeginsWith(FilamentPage::appointments());
});

it('can navigate to services resource page via sidebar', function () {
    $page = adminLogin();

    $page->clickText('Services')
        ->assertPathBeginsWith(FilamentPage::services());
});

it('can navigate to events resource page via sidebar', function () {
    $page = adminLogin();

    $page->clickText('Events')
        ->assertPathBeginsWith(FilamentPage::events());
});

it('can navigate to trainings resource page via sidebar', function () {
    $page = adminLogin();

    $page->clickText('Trainings')
        ->assertPathBeginsWith(FilamentPage::trainings());
});

it('can navigate to urgent bookings resource page via sidebar', function () {
    $page = adminLogin();

    $page->clickText('Urgent Bookings')
        ->assertPathBeginsWith(FilamentPage::urgentBookings());
});

it('can navigate to testimonials resource page via sidebar', function () {
    $page = adminLogin();

    $page->clickText('Testimonials')
        ->assertPathBeginsWith(FilamentPage::testimonials());
});
