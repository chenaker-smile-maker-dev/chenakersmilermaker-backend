<?php

use App\Models\Patient;
use Illuminate\Support\Facades\Mail;

// ─── Register ─────────────────────────────────────────────────────────────────

it('patient can register', function () {
    Mail::fake();

    $response = $this->postJson('/api/v1/patient/auth/register', [
        'first_name'            => 'Ali',
        'last_name'             => 'Ben Salah',
        'email'                 => 'ali@example.com',
        'phone'                 => '0551234567',
        'age'                   => 30,
        'gender'                => 'male',
        'password'              => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonStructure(['data' => ['token', 'refresh_token', 'patient']]);

    $this->assertDatabaseHas('patients', ['email' => 'ali@example.com']);
});

it('register validates required fields', function () {
    $this->postJson('/api/v1/patient/auth/register', [])
        ->assertStatus(422);
});

it('register rejects duplicate email', function () {
    Mail::fake();
    Patient::factory()->create(['email' => 'duplicate@example.com']);

    $this->postJson('/api/v1/patient/auth/register', [
        'first_name'            => 'Test',
        'last_name'             => 'User',
        'email'                 => 'duplicate@example.com',
        'phone'                 => '0551234568',
        'age'                   => 25,
        'gender'                => 'male',
        'password'              => 'password123',
        'password_confirmation' => 'password123',
    ])->assertStatus(422);
});

// ─── Login ─────────────────────────────────────────────────────────────────────

it('patient can login', function () {
    $patient = Patient::factory()->create(['password' => 'password']);

    $this->postJson('/api/v1/patient/auth/login', [
        'email'    => $patient->email,
        'password' => 'password',
    ])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonStructure(['data' => ['token', 'refresh_token', 'patient']]);
});

it('login fails with wrong password', function () {
    $patient = Patient::factory()->create();

    $this->postJson('/api/v1/patient/auth/login', [
        'email'    => $patient->email,
        'password' => 'wrong_password',
    ])
        ->assertStatus(401)
        ->assertJsonPath('success', false);
});

it('login fails with unknown email', function () {
    $this->postJson('/api/v1/patient/auth/login', [
        'email'    => 'nobody@example.com',
        'password' => 'password',
    ])->assertStatus(401);
});

// ─── Response has multilang message ───────────────────────────────────────────

it('login response message contains all 3 locales', function () {
    $patient = Patient::factory()->create(['password' => 'password']);

    $response = $this->postJson('/api/v1/patient/auth/login', [
        'email'    => $patient->email,
        'password' => 'password',
    ]);

    $response->assertOk();
    expect($response->json('message'))->toBeMultilang();
});

it('error response message contains all 3 locales', function () {
    $patient = Patient::factory()->create();

    $response = $this->postJson('/api/v1/patient/auth/login', [
        'email'    => $patient->email,
        'password' => 'wrong_password',
    ]);

    $response->assertStatus(401);
    expect($response->json('message'))->toBeMultilang();
});

// ─── Logout ────────────────────────────────────────────────────────────────────

it('patient can logout', function () {
    $patient = Patient::factory()->create();

    $this->actAsPatient($patient)
        ->postJson('/api/v1/patient/auth/logout')
        ->assertOk()
        ->assertJsonPath('success', true);
});

it('logout requires authentication', function () {
    $this->postJson('/api/v1/patient/auth/logout')
        ->assertStatus(401);
});

// ─── Email verification ────────────────────────────────────────────────────────

it('email verification works with valid token', function () {
    $patient = Patient::factory()->create([
        'email_verification_token' => 'valid-token-abc123',
    ]);

    $this->postJson('/api/v1/patient/auth/verify-email', [
        'email' => $patient->email,
        'token' => 'valid-token-abc123',
    ])
        ->assertOk()
        ->assertJsonPath('success', true);

    expect($patient->fresh()->email_verified_at)->not()->toBeNull();
});

it('email verification fails with invalid token', function () {
    $patient = Patient::factory()->create([
        'email_verification_token' => 'correct-token',
    ]);

    $this->postJson('/api/v1/patient/auth/verify-email', [
        'email' => $patient->email,
        'token' => 'wrong-token',
    ])
        ->assertStatus(422)
        ->assertJsonPath('success', false);
});

// ─── Resend verification ───────────────────────────────────────────────────────

it('resends verification email', function () {
    Mail::fake();
    $patient = Patient::factory()->unverified()->create();

    $this->actAsPatient($patient)
        ->postJson('/api/v1/patient/auth/resend-verification')
        ->assertOk()
        ->assertJsonPath('success', true);
});

it('resend returns success if already verified', function () {
    $patient = Patient::factory()->verified()->create();

    $this->actAsPatient($patient)
        ->postJson('/api/v1/patient/auth/resend-verification')
        ->assertOk()
        ->assertJsonPath('success', true);
});

it('resend is rate limited', function () {
    Mail::fake();
    $patient = Patient::factory()->unverified()->create([
        'email_verification_sent_at' => now()->subSeconds(30),
    ]);

    $this->actAsPatient($patient)
        ->postJson('/api/v1/patient/auth/resend-verification')
        ->assertStatus(429);
});
