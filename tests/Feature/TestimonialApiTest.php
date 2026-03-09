<?php

use App\Models\Testimonial;

it('can list testimonials', function () {
    Testimonial::factory()->count(3)->create(['is_published' => true, 'deleted_at' => null]);

    $this->getJson('/api/v1/testimonials/')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonStructure([
            'data' => [
                'data'       => [['id', 'name', 'content', 'rating']],
                'pagination' => ['total', 'per_page', 'current_page'],
            ],
        ]);
});

it('unpublished testimonials are not listed', function () {
    Testimonial::factory()->count(2)->create(['is_published' => true,  'deleted_at' => null]);
    Testimonial::factory()->count(3)->create(['is_published' => false, 'deleted_at' => null]);

    $response = $this->getJson('/api/v1/testimonials/');

    $response->assertOk();
    expect($response->json('data.pagination.total'))->toBe(2);
});

it('can show single testimonial', function () {
    $testimonial = Testimonial::factory()->create(['is_published' => true, 'deleted_at' => null]);

    $this->getJson("/api/v1/testimonials/{$testimonial->id}")
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.id', $testimonial->id)
        ->assertJsonStructure(['data' => ['id', 'name', 'content', 'rating']]);
});

it('soft-deleted testimonial returns 404', function () {
    $testimonial = Testimonial::factory()->create([
        'is_published' => true,
        'deleted_at'   => now()->subDay(),
    ]);

    $this->getJson("/api/v1/testimonials/{$testimonial->id}")
        ->assertStatus(404);
});

it('list is paginated', function () {
    Testimonial::factory()->count(15)->create(['is_published' => true, 'deleted_at' => null]);

    $response = $this->getJson('/api/v1/testimonials/?per_page=5&page=1');

    $response->assertOk();
    expect($response->json('data.data'))->toHaveCount(5);
    expect($response->json('data.pagination.total'))->toBe(15);
});
