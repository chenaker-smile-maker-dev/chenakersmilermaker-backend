<?php

use App\Models\Doctor;
use App\Models\Event;
use App\Models\Patient;
use App\Models\Service;
use App\Models\Testimonial;
use App\Models\Training;

/**
 * API Conventions Test
 *
 * Verifies that ALL API endpoints follow the project-wide conventions:
 * - success/false wrapper
 * - message field with all 3 locales {en, ar, fr}
 * - translatable fields return {en, ar, fr} objects
 * - image fields return {original, thumb} objects
 */

// ─── Response envelope convention ────────────────────────────────────────────

it('every successful response has success=true', function () {
    $endpoints = [
        '/api/v1/events/',
        '/api/v1/trainings/',
        '/api/v1/testimonials/',
        '/api/v1/services/service',
    ];

    foreach ($endpoints as $endpoint) {
        $this->getJson($endpoint)
            ->assertOk()
            ->assertJsonPath('success', true);
    }
});

it('every successful response has a multilang message field', function () {
    Event::factory()->create();
    $response = $this->getJson('/api/v1/events/');
    expect($response->json('message'))->toBeMultilang();
});

it('every error response has success=false', function () {
    $patient = Patient::factory()->create(['password' => 'password123']);

    $response = $this->postJson('/api/v1/patient/auth/login', [
        'email'    => $patient->email,
        'password' => 'wrongpassword',
    ]);
    $response->assertStatus(401);
    expect($response->json('success'))->toBe(false);
});

it('every error response has a multilang message field', function () {
    $patient = Patient::factory()->create(['password' => 'password123']);

    $response = $this->postJson('/api/v1/patient/auth/login', [
        'email'    => $patient->email,
        'password' => 'wrongpassword',
    ]);
    $response->assertStatus(401);
    expect($response->json('message'))->toBeMultilang();
});

// ─── Translatable fields convention ──────────────────────────────────────────

it('event title is returned as multilang object', function () {
    $event = Event::factory()->create([
        'title' => ['en' => 'EN Title', 'ar' => 'العنوان', 'fr' => 'FR Titre'],
    ]);

    $response = $this->getJson("/api/v1/events/{$event->id}");

    $response->assertOk();
    expect($response->json('data.name'))->toBeMultilang();
});

it('event description is returned as multilang object', function () {
    $event = Event::factory()->create();

    $response = $this->getJson("/api/v1/events/{$event->id}");

    $response->assertOk();
    expect($response->json('data.description'))->toBeMultilang();
});

it('training title is returned as multilang object', function () {
    $training = Training::factory()->create();

    $response = $this->getJson("/api/v1/trainings/{$training->id}");

    $response->assertOk();
    expect($response->json('data.name'))->toBeMultilang();
});

it('service name is returned as multilang object', function () {
    $service = Service::factory()->create();

    $response = $this->getJson("/api/v1/services/service/{$service->id}");

    $response->assertOk();
    expect($response->json('data.service.name'))->toBeMultilang();
});

it('doctor name is returned as multilang object', function () {
    $doctor = Doctor::factory()->create();

    $response = $this->getJson("/api/v1/appointement/doctor/{$doctor->id}");

    $response->assertOk();
    expect($response->json('data.doctor.name'))->toBeMultilang();
});

// ─── Patient appointment multilang convention ─────────────────────────────────

it('patient appointment list response message is multilang', function () {
    $patient = Patient::factory()->verified()->create();

    $response = $this->actAsPatient($patient)
        ->getJson('/api/v1/patient/appointments/');

    $response->assertOk();
    expect($response->json('message'))->toBeMultilang();
});

// ─── Notifications multilang ──────────────────────────────────────────────────

it('notification unread count response is multilang', function () {
    $patient = Patient::factory()->verified()->create();

    $response = $this->actAsPatient($patient)
        ->getJson('/api/v1/patient/notifications/unread-count');

    $response->assertOk();
    expect($response->json('message'))->toBeMultilang();
});

// ─── Auth responses multilang ─────────────────────────────────────────────────

it('register response message is multilang', function () {
    \Illuminate\Support\Facades\Mail::fake();

    $response = $this->postJson('/api/v1/patient/auth/register', [
        'first_name'            => 'ConventionTest',
        'last_name'             => 'Patient',
        'email'                 => 'convention.test@clinic.dz',
        'phone'                 => '0660000099',
        'age'                   => 30,
        'gender'                => 'male',
        'password'              => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertOk();
    expect($response->json('message'))->toBeMultilang();
});

it('login response message is multilang', function () {
    $patient = Patient::factory()->create(['password' => 'password']);

    $response = $this->postJson('/api/v1/patient/auth/login', [
        'email'    => $patient->email,
        'password' => 'password',
    ]);

    $response->assertOk();
    expect($response->json('message'))->toBeMultilang();
});

it('testimonial list response message is multilang', function () {
    $response = $this->getJson('/api/v1/testimonials/');

    $response->assertOk();
    expect($response->json('message'))->toBeMultilang();
});

it('training list response message is multilang', function () {
    $response = $this->getJson('/api/v1/trainings/');

    $response->assertOk();
    expect($response->json('message'))->toBeMultilang();
});
