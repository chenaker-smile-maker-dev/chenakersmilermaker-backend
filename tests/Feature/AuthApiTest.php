<?php

namespace Tests\Feature;

use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    // ─── Register ────────────────────────────────────────────────────────────

    public function test_patient_can_register(): void
    {
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
    }

    public function test_register_validates_required_fields(): void
    {
        $response = $this->postJson('/api/v1/patient/auth/register', []);

        $response->assertStatus(422);
    }

    public function test_register_rejects_duplicate_email(): void
    {
        Mail::fake();
        Patient::factory()->create(['email' => 'duplicate@example.com']);

        $response = $this->postJson('/api/v1/patient/auth/register', [
            'first_name'            => 'Test',
            'last_name'             => 'User',
            'email'                 => 'duplicate@example.com',
            'phone'                 => '0551234568',
            'age'                   => 25,
            'gender'                => 'male',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422);
    }

    // ─── Login ───────────────────────────────────────────────────────────────

    public function test_patient_can_login(): void
    {
        $patient = Patient::factory()->create(['password' => 'password']);

        $response = $this->postJson('/api/v1/patient/auth/login', [
            'email'    => $patient->email,
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['token', 'refresh_token', 'patient']]);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $patient = Patient::factory()->create();

        $response = $this->postJson('/api/v1/patient/auth/login', [
            'email'    => $patient->email,
            'password' => 'wrong_password',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('success', false);
    }

    public function test_login_fails_with_unknown_email(): void
    {
        $response = $this->postJson('/api/v1/patient/auth/login', [
            'email'    => 'nobody@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(401);
    }

    // ─── Logout ──────────────────────────────────────────────────────────────

    public function test_patient_can_logout(): void
    {
        $patient = Patient::factory()->create();

        $response = $this->actAsPatient($patient)
            ->postJson('/api/v1/patient/auth/logout');

        $response->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_logout_requires_authentication(): void
    {
        $response = $this->postJson('/api/v1/patient/auth/logout');

        $response->assertStatus(401);
    }

    // ─── Email verification ───────────────────────────────────────────────────

    public function test_email_verification_works(): void
    {
        $patient = Patient::factory()->create([
            'email_verification_token' => 'valid-token-abc123',
        ]);

        $response = $this->postJson('/api/v1/patient/auth/verify-email', [
            'email' => $patient->email,
            'token' => 'valid-token-abc123',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertNotNull($patient->fresh()->email_verified_at);
    }

    public function test_email_verification_fails_with_invalid_token(): void
    {
        $patient = Patient::factory()->create([
            'email_verification_token' => 'correct-token',
        ]);

        $response = $this->postJson('/api/v1/patient/auth/verify-email', [
            'email' => $patient->email,
            'token' => 'wrong-token',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    // ─── Resend verification ──────────────────────────────────────────────────

    public function test_resend_verification_email(): void
    {
        Mail::fake();
        $patient = Patient::factory()->unverified()->create();

        $response = $this->actAsPatient($patient)
            ->postJson('/api/v1/patient/auth/resend-verification');

        $response->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_resend_verification_returns_message_if_already_verified(): void
    {
        $patient = Patient::factory()->verified()->create();

        $response = $this->actAsPatient($patient)
            ->postJson('/api/v1/patient/auth/resend-verification');

        $response->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_resend_verification_is_rate_limited(): void
    {
        Mail::fake();
        $patient = Patient::factory()->unverified()->create([
            'email_verification_sent_at' => now()->subSeconds(30), // sent 30s ago, limit is 1 min
        ]);

        $response = $this->actAsPatient($patient)
            ->postJson('/api/v1/patient/auth/resend-verification');

        $response->assertStatus(429);
    }
}
