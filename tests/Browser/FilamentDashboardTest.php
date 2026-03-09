<?php

use App\Models\User;

/**
 * Helper: log in as a freshly-created admin user and return the page
 * already sitting on the Filament dashboard.
 */
function loginToDashboard(): \Pest\Browser\Api\Webpage
{
    $user = User::factory()->create([
        'email'    => 'dash-admin@example.com',
        'password' => bcrypt('password'),
    ]);

    /** @var Tests\TestCase $test */
    $test = test();

    return $test->visit('/admin/login')
        ->type('email', 'dash-admin@example.com')
        ->type('password', 'password')
        ->press('Sign in');
}

// ─── Dashboard ────────────────────────────────────────────────────────────────

it('dashboard loads after login', function () {
    $page = loginToDashboard();

    $page->assertPathIs('/admin');
});

it('dashboard shows the navigation sidebar', function () {
    $page = loginToDashboard();

    $page->assertPathIs('/admin')
        ->assertPresent('nav');
});

it('dashboard page title contains the app name', function () {
    $page = loginToDashboard();

    $page->assertPathIs('/admin')
        ->assertTitleContains(config('app.name'));
});

it('can navigate to the patients resource page', function () {
    $page = loginToDashboard();

    $page->assertPathIs('/admin')
        ->click('Patients')
        ->assertPathBeginsWith('/admin/patients');
});
