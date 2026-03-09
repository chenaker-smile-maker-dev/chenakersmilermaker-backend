<?php

namespace Tests\Feature;

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class EventApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::create(2026, 6, 15, 12, 0));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_can_list_events(): void
    {
        Event::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/events/');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'data' => [['id', 'name', 'description', 'date', 'status']],
                    'pagination' => ['total', 'per_page', 'current_page', 'last_page'],
                ],
            ]);
    }

    public function test_list_events_returns_empty_when_none_exist(): void
    {
        $response = $this->getJson('/api/v1/events/');

        $response->assertOk()
            ->assertJsonPath('data.pagination.total', 0);
    }

    public function test_can_filter_future_events(): void
    {
        Event::factory()->future()->count(2)->create();
        Event::factory()->archived()->count(3)->create();

        $response = $this->getJson('/api/v1/events/?type=future');

        $response->assertOk();
        $this->assertCount(2, $response->json('data.data'));
    }

    public function test_can_filter_archived_events(): void
    {
        Event::factory()->future()->count(2)->create();
        Event::factory()->archived()->count(3)->create();

        $response = $this->getJson('/api/v1/events/?type=archive');

        $response->assertOk();
        $this->assertCount(3, $response->json('data.data'));
    }

    public function test_can_filter_happening_events(): void
    {
        Event::factory()->happening()->count(2)->create();
        Event::factory()->future()->count(3)->create();

        $response = $this->getJson('/api/v1/events/?type=happening');

        $response->assertOk();
        $this->assertCount(2, $response->json('data.data'));
    }

    public function test_no_type_filter_returns_all_non_deleted_events(): void
    {
        Event::factory()->future()->count(2)->create();
        Event::factory()->archived()->count(2)->create();
        Event::factory()->happening()->count(1)->create();

        $response = $this->getJson('/api/v1/events/');

        $response->assertOk();
        $this->assertCount(5, $response->json('data.data'));
    }

    public function test_can_show_single_event(): void
    {
        $event = Event::factory()->future()->create();

        $response = $this->getJson("/api/v1/events/{$event->id}");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $event->id)
            ->assertJsonStructure(['data' => ['id', 'name', 'description', 'date', 'status', 'pictures']]);
    }

    public function test_soft_deleted_event_returns_404(): void
    {
        $event = Event::factory()->create(['deleted_at' => now()->subDay()]);

        $response = $this->getJson("/api/v1/events/{$event->id}");

        $response->assertStatus(404);
    }

    public function test_events_list_is_paginated(): void
    {
        Event::factory()->future()->count(15)->create();

        $response = $this->getJson('/api/v1/events/?per_page=5&page=1');

        $response->assertOk();
        $this->assertCount(5, $response->json('data.data'));
        $this->assertEquals(15, $response->json('data.pagination.total'));
        $this->assertEquals(3, $response->json('data.pagination.last_page'));
    }

    public function test_event_response_includes_multilang_fields(): void
    {
        $event = Event::factory()->future()->create();

        $response = $this->getJson("/api/v1/events/{$event->id}");

        $data = $response->json('data');
        $this->assertIsArray($data['name']);
        $this->assertArrayHasKey('en', $data['name']);
        $this->assertArrayHasKey('ar', $data['name']);
        $this->assertArrayHasKey('fr', $data['name']);
    }
}
