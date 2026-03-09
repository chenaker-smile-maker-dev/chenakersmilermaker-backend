<?php

use App\Models\Patient;
use App\Models\Review;
use App\Models\Training;
use Illuminate\Support\Facades\Notification;

// ─── List ─────────────────────────────────────────────────────────────────────

it('can list trainings', function () {
    Training::factory()->count(3)->create();

    $this->getJson('/api/v1/trainings/')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonStructure(['data' => ['data', 'pagination']]);
});

it('list trainings is paginated', function () {
    Training::factory()->count(12)->create();

    $response = $this->getJson('/api/v1/trainings/?per_page=5&page=1');

    $response->assertOk();
    expect($response->json('data.data'))->toHaveCount(5);
    expect($response->json('data.pagination.total'))->toBe(12);
});

// ─── Show ─────────────────────────────────────────────────────────────────────

it('can show training', function () {
    $training = Training::factory()->create();

    $this->getJson("/api/v1/trainings/{$training->id}")
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.id', $training->id);
});

it('show training includes approved reviews', function () {
    $training = Training::factory()->create();
    Review::factory()->approved()->forTraining($training)->count(2)->create();
    Review::factory()->pending()->forTraining($training)->count(1)->create();

    $response = $this->getJson("/api/v1/trainings/{$training->id}");

    $response->assertOk();
    expect($response->json('data.reviews'))->toHaveCount(2);
});

it('training response includes multilang fields', function () {
    $training = Training::factory()->create();

    $response = $this->getJson("/api/v1/trainings/{$training->id}");

    $response->assertOk();
    expect($response->json('data'))->toHaveKey('name');
});

it('soft-deleted training returns 404', function () {
    $training = Training::factory()->create(['deleted_at' => now()->subDay()]);

    $this->getJson("/api/v1/trainings/{$training->id}")
        ->assertStatus(404);
});

// ─── Submit review ────────────────────────────────────────────────────────────

it('authenticated patient can submit review', function () {
    Notification::fake();
    $training = Training::factory()->create();
    $patient  = Patient::factory()->verified()->create();

    $this->actAsPatient($patient)
        ->postJson("/api/v1/trainings/{$training->id}/reviews", [
            'content' => 'This training was absolutely fantastic and very helpful!',
            'rating'  => 5,
        ])
        ->assertCreated()
        ->assertJsonPath('success', true);

    $this->assertDatabaseHas('reviews', [
        'reviewable_type' => Training::class,
        'reviewable_id'   => $training->id,
        'rating'          => 5,
        'is_approved'     => 0,
    ]);
});

it('review submission requires authentication', function () {
    $training = Training::factory()->create();

    $this->postJson("/api/v1/trainings/{$training->id}/reviews", [
        'content' => 'Great training session overall.',
        'rating'  => 4,
    ])->assertStatus(401);
});

it('review content must be at least 10 characters', function () {
    $training = Training::factory()->create();
    $patient  = Patient::factory()->verified()->create();

    $this->actAsPatient($patient)
        ->postJson("/api/v1/trainings/{$training->id}/reviews", [
            'content' => 'Short',
            'rating'  => 4,
        ])->assertStatus(422);
});

it('review rating must be between 1 and 5', function () {
    $training = Training::factory()->create();
    $patient  = Patient::factory()->verified()->create();

    $this->actAsPatient($patient)
        ->postJson("/api/v1/trainings/{$training->id}/reviews", [
            'content' => 'This is a long enough review content.',
            'rating'  => 6,
        ])->assertStatus(422);

    $this->actAsPatient($patient)
        ->postJson("/api/v1/trainings/{$training->id}/reviews", [
            'content' => 'This is a long enough review content.',
            'rating'  => 0,
        ])->assertStatus(422);
});

it('new review is pending approval', function () {
    Notification::fake();
    $training = Training::factory()->create();
    $patient  = Patient::factory()->verified()->create();

    $this->actAsPatient($patient)
        ->postJson("/api/v1/trainings/{$training->id}/reviews", [
            'content' => 'Amazing training content and excellent instructor.',
            'rating'  => 5,
        ])->assertCreated();

    $this->assertDatabaseHas('reviews', [
        'reviewable_id' => $training->id,
        'is_approved'   => 0,
    ]);
});
