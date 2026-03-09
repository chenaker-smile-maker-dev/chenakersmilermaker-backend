<?php

use App\Models\User;

// ─── Login page ───────────────────────────────────────────────────────────────

it('redirects to login page when accessing admin unauthenticated', function () {
    $page = $this->visit('/admin');

    $page->assertPathIs('/admin/login');
});

it('shows the login form on the admin login page', function () {
    $page = $this->visit('/admin/login');

    $page->assertPathIs('/admin/login')
        ->assertSee('Sign in')
        ->assertPresent('input[name="email"]')
        ->assertPresent('input[name="password"]');
});

// ─── Login flow ───────────────────────────────────────────────────────────────

it('admin user can log in to filament panel', function () {
    $user = User::factory()->create([
        'email'    => 'test-admin@example.com',
        'password' => bcrypt('password'),
    ]);

    $page = $this->visit('/admin/login');

    $page->type('email', 'test-admin@example.com')
        ->type('password', 'password')
        ->press('Sign in')
        ->assertPathIs('/admin');
});

it('shows error for invalid credentials', function () {
    $page = $this->visit('/admin/login');

    $page->type('email', 'nobody@example.com')
        ->type('password', 'wrong-password')
        ->press('Sign in')
        ->assertPathIs('/admin/login')
        ->assertSee('credentials');
});
