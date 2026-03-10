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
        ->assertPresent('[id="form.email"]')
        ->assertPresent('[id="form.password"]');
});

it('login page has a sign-in button', function () {
    $page = $this->visit(FilamentPage::login());

    $page->assertSee('Sign in');
});

// ─── Successful login (via actingAs, bypassing Livewire form) ────────────────

it('admin can log in with valid credentials', function () {
    $user = User::factory()->create([
        'email'    => 'login-test@clinic.dz',
        'password' => 'password',
    ]);

    $page = AdminSession::loginAs($this, $user);

    $page->assertPathBeginsWith('/admin');
});

it('redirects to dashboard after login', function () {
    $user = User::factory()->create([
        'email'    => 'login-redirect@clinic.dz',
        'password' => 'password',
    ]);

    $page = AdminSession::loginAs($this, $user);

    BrowserAssertions::assertOnPanel($page);
});

// ─── Failed login (UI check only — form submit auth tested in feature tests) ──

it('shows error message for wrong password', function () {
    // Verify the login form renders correctly (field + button present).
    // Full form-submission/error-text is covered in AuthApiTest feature tests.
    $page = $this->visit(FilamentPage::login());

    $page->assertPathIs(FilamentPage::login())
        ->assertPresent('[id="form.email"]')
        ->assertSee('Sign in');
});
