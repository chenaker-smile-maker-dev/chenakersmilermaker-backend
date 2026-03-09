<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TestimonialApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_testimonials(): void
    {
        Testimonial::factory()->count(3)->create([
            'is_published' => true,
            'deleted_at'   => null,
        ]);

        $response = $this->getJson('/api/v1/testimonials/');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'data'       => [['id', 'name', 'content', 'rating']],
                    'pagination' => ['total', 'per_page', 'current_page'],
                ],
            ]);

        $this->assertEquals(3, $response->json('data.pagination.total'));
    }

    public function test_unpublished_testimonials_are_not_listed(): void
    {
        Testimonial::factory()->count(2)->create(['is_published' => true,  'deleted_at' => null]);
        Testimonial::factory()->count(3)->create(['is_published' => false, 'deleted_at' => null]);

        $response = $this->getJson('/api/v1/testimonials/');

        $response->assertOk();
        $this->assertEquals(2, $response->json('data.pagination.total'));
    }

    public function test_can_show_single_testimonial(): void
    {
        $testimonial = Testimonial::factory()->create([
            'is_published' => true,
            'deleted_at'   => null,
        ]);

        $response = $this->getJson("/api/v1/testimonials/{$testimonial->id}");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $testimonial->id)
            ->assertJsonStructure(['data' => ['id', 'name', 'content', 'rating']]);
    }

    public function test_soft_deleted_testimonial_returns_404(): void
    {
        $testimonial = Testimonial::factory()->create([
            'is_published' => true,
            'deleted_at'   => now()->subDay(),
        ]);

        $response = $this->getJson("/api/v1/testimonials/{$testimonial->id}");

        $response->assertStatus(404);
    }

    public function test_list_is_paginated(): void
    {
        Testimonial::factory()->count(15)->create([
            'is_published' => true,
            'deleted_at'   => null,
        ]);

        $response = $this->getJson('/api/v1/testimonials/?per_page=5&page=1');

        $response->assertOk();
        $this->assertCount(5, $response->json('data.data'));
        $this->assertEquals(15, $response->json('data.pagination.total'));
    }
}
