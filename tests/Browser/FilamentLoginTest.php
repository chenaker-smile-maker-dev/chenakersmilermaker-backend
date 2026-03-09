<?php

use App\Models\User;
use Tests\Browser\Core\AdminSession;
use Tests\Browser\Core\BrowserAssertions;
use Tests\Browser\Core\FilamentPage;

// ─── Redirect unauthenticated ─────────────────────────────────────────────────

it('redirects to login when visiting admin panel unauthenticated', function () {
    $page = $this->visit(FilamentPage::base());

    BrowserAssertions::assertRedirectedToLogin($page);
});

it('redirects to login when visiting resource pages unauthenticated', function () {
    foreach ([FilamentPage::patients(), FilamentPage::doctors(), FilamentPage::appointments()] as $path) {
        $page = $this->visit($path);
        BrowserAssertions::assertRedirectedToLogin($page);
    }
});

// ─── Login page UI ────────────────────────────────────────────────────────────

it('shows the login form with email and password fields', function () {
    $page = $this->visit(FilamentPage::login());

    $page->assertPathIs(FilamentPage::login())
        ->assertPresent('input[name="email"]')
        ->assertPresent('input[name="password"]');
});

it('login page has a sign-in button', function () {
    $page = $this->visit(FilamentPage::login());

    $page->assertSee('Sign in');
});

// ─── Successful login ─────────────────────────────────────────────────────────

it('admin can log in with valid credentials', function () {
    $user = User::factory()->create([
        'email'    => 'login-test@clinic.dz',
        'password' => bcrypt('password'),
    ]);

    $page = AdminSession::loginAs($this, $user);

    $page->assertPathIs(FilamentPage::dashboard());
});

it('redirects to dashboard after login', function () {
    $user = User::factory()->create([
        'email'    => 'login-redirect@clinic.dz',
        'password' => bcrypt('password'),
    ]);

    $page = AdminSession::loginAs($this, $user);

    BrowserAssertions::assertOnPanel($page);
});

// ─── Failed login ─────────────────────────────────────────────────────────────

it('shows error message for wrong password', function () {
    User::factory()->create([
        'email'    => 'wrong-pass@clinic.dz',
        'password' => bcrypt('correct-password'),
    ]);

    $page = $this->visit(FilamentPage::login())
        ->type('email', 'wrong-pass@clinic.dz')
        ->type('password', 'wrong-password')
        ->press('Sign in');

    $page->assertPathIs(FilamentPage::login())
        ->assertSee('credentials');
});

it('stays on login page after failed login with unknown email', function () {
    $page = $this->visit(FilamentPage::login())
        ->type('email', 'nobody@clinic.dz')
        ->type('password', 'password')
        ->press('Sign in');

    $page->assertPathIs(FilamentPage::login());
});
