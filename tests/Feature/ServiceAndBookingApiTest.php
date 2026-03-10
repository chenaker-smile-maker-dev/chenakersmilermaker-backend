<?php

use App\Models\Doctor;
use App\Models\Service;

// ─── List services ────────────────────────────────────────────────────────────

it('can list services', function () {
    Service::factory()->count(3)->create(['active' => true]);

    $this->getJson('/api/v1/services/service')
        ->assertOk()
        ->assertJsonPath('success', true);
});

it('list services response has multilang message', function () {
    $response = $this->getJson('/api/v1/services/service');

    $response->assertOk();
    expect($response->json('message'))->toBeMultilang();
});

it('can show a single service', function () {
    $service = Service::factory()->create();

    $this->getJson("/api/v1/services/service/{$service->id}")
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.service.id', $service->id);
});

it('show unknown service returns 404', function () {
    $this->getJson('/api/v1/services/service/99999')
        ->assertStatus(404);
});

// ─── List doctors (via booking endpoint) ──────────────────────────────────────

it('can list doctors for booking', function () {
    Doctor::factory()->count(2)->create();

    $this->getJson('/api/v1/appointement/doctor')
        ->assertOk()
        ->assertJsonPath('success', true);
});

it('can show a single doctor for booking', function () {
    $doctor = Doctor::factory()->create();

    $this->getJson("/api/v1/appointement/doctor/{$doctor->id}")
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.doctor.id', $doctor->id);
});

it('doctor availability endpoint requires valid doctor and service', function () {
    $doctor  = Doctor::factory()->create();
    $service = Service::factory()->create(['active' => true]);
    $doctor->services()->attach($service->id);

    $this->getJson("/api/v1/appointement/{$doctor->id}/{$service->id}/availability")
        ->assertOk();
});

it('check availability endpoint exists and returns a response', function () {
    $doctor  = Doctor::factory()->create();
    $service = Service::factory()->create(['duration' => 60]);
    $doctor->services()->attach($service->id);

    $response = $this->postJson("/api/v1/booking/{$doctor->id}/{$service->id}/check-availability", [
        'from' => now()->addDays(5)->setHour(10)->setMinute(0)->toDateTimeString(),
        'to'   => now()->addDays(5)->setHour(11)->setMinute(0)->toDateTimeString(),
    ]);

    // The endpoint exists (not 404/405)
    expect($response->status())->not()->toBeIn([404, 405]);
});
