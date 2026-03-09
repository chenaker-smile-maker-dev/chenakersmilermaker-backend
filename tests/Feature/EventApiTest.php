<?php

use App\Models\Event;

it('can list events', function () {
    Event::factory()->count(3)->create();

    $this->getJson('/api/v1/events/')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonStructure(['data' => ['data', 'pagination']]);
});

it('can filter future events', function () {
    \Carbon\Carbon::setTestNow(\Carbon\Carbon::create(2026, 6, 15, 12, 0));
    Event::factory()->future()->count(2)->create();
    Event::factory()->archived()->count(3)->create();

    $response = $this->getJson('/api/v1/events/?type=future');

    $response->assertOk();
    expect($response->json('data.pagination.total'))->toBe(2);
    \Carbon\Carbon::setTestNow();
});

it('can filter archived events', function () {
    \Carbon\Carbon::setTestNow(\Carbon\Carbon::create(2026, 6, 15, 12, 0));
    Event::factory()->future()->count(2)->create();
    Event::factory()->archived()->count(3)->create();

    $response = $this->getJson('/api/v1/events/?type=archive');

    $response->assertOk();
    expect($response->json('data.pagination.total'))->toBe(3);
    \Carbon\Carbon::setTestNow();
});

it('can show single event', function () {
    $event = Event::factory()->create();

    $this->getJson("/api/v1/events/{$event->id}")
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.id', $event->id);
});

it('returns 404 for non-existent event', function () {
    $this->getJson('/api/v1/events/99999')
        ->assertStatus(404);
});

it('list is paginated', function () {
    Event::factory()->count(15)->create();

    $response = $this->getJson('/api/v1/events/?per_page=5&page=1');

    $response->assertOk();
    expect($response->json('data.data'))->toHaveCount(5);
    expect($response->json('data.pagination.total'))->toBe(15);
});

it('event response includes multilang fields', function () {
    $event = Event::factory()->create();

    $response = $this->getJson("/api/v1/events/{$event->id}");

    $response->assertOk();
    // Translatable fields should expose multiple locales
    expect($response->json('data'))->toHaveKey('name');
});

it('event list response message contains all 3 locales', function () {
    $response = $this->getJson('/api/v1/events/');

    $response->assertOk();
    expect($response->json('message'))->toBeMultilang();
});
