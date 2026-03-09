<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\Review;
use App\Models\Training;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TrainingApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_trainings(): void
    {
        Training::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/trainings/');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'data' => [['id', 'name', 'description', 'price', 'trainer_name', 'duration']],
                    'pagination' => ['total', 'per_page', 'current_page'],
                ],
            ]);
    }

    public function test_list_trainings_is_paginated(): void
    {
        Training::factory()->count(15)->create();

        $response = $this->getJson('/api/v1/trainings/?per_page=5&page=1');

        $response->assertOk();
        $this->assertCount(5, $response->json('data.data'));
        $this->assertEquals(15, $response->json('data.pagination.total'));
    }

    public function test_can_show_training(): void
    {
        $training = Training::factory()->create();

        $response = $this->getJson("/api/v1/trainings/{$training->id}");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $training->id)
            ->assertJsonStructure(['data' => ['id', 'name', 'description', 'price', 'trainer_name', 'reviews']]);
    }

    public function test_show_training_includes_approved_reviews(): void
    {
        $training = Training::factory()->create();

        // Create 2 approved, 1 pending review
        Review::factory()->approved()->forTraining($training)->count(2)->create();
        Review::factory()->pending()->forTraining($training)->count(1)->create();

        $response = $this->getJson("/api/v1/trainings/{$training->id}");

        $response->assertOk();
        $this->assertCount(2, $response->json('data.reviews'));
    }

    public function test_training_response_includes_multilang_fields(): void
    {
        $training = Training::factory()->create();

        $response = $this->getJson("/api/v1/trainings/{$training->id}");

        $data = $response->json('data');
        $this->assertIsArray($data['name']);
        $this->assertArrayHasKey('en', $data['name']);
        $this->assertArrayHasKey('ar', $data['name']);
        $this->assertArrayHasKey('fr', $data['name']);
    }

    public function test_soft_deleted_training_returns_404(): void
    {
        $training = Training::factory()->create(['deleted_at' => now()->subDay()]);

        $response = $this->getJson("/api/v1/trainings/{$training->id}");

        $response->assertStatus(404);
    }

    // ─── Submit review ────────────────────────────────────────────────────────

    public function test_authenticated_patient_can_submit_review(): void
    {
        Notification::fake();
        $training = Training::factory()->create();
        $patient  = Patient::factory()->verified()->create();

        $response = $this->actAsPatient($patient)
            ->postJson("/api/v1/trainings/{$training->id}/reviews", [
                'content' => 'This training was absolutely fantastic and very helpful!',
                'rating'  => 5,
            ]);

        $response->assertCreated()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('reviews', [
            'reviewable_type' => Training::class,
            'reviewable_id'   => $training->id,
            'rating'          => 5,
            'is_approved'     => 0,
        ]);
    }

    public function test_review_submission_requires_authentication(): void
    {
        $training = Training::factory()->create();

        $response = $this->postJson("/api/v1/trainings/{$training->id}/reviews", [
            'content' => 'Great training session overall.',
            'rating'  => 4,
        ]);

        $response->assertStatus(401);
    }

    public function test_review_content_must_be_at_least_10_characters(): void
    {
        $training = Training::factory()->create();
        $patient  = Patient::factory()->verified()->create();

        $response = $this->actAsPatient($patient)
            ->postJson("/api/v1/trainings/{$training->id}/reviews", [
                'content' => 'Short',
                'rating'  => 4,
            ]);

        $response->assertStatus(422);
    }

    public function test_review_rating_must_be_between_1_and_5(): void
    {
        $training = Training::factory()->create();
        $patient  = Patient::factory()->verified()->create();

        $this->actAsPatient($patient)
            ->postJson("/api/v1/trainings/{$training->id}/reviews", [
                'content' => 'This is a long enough review content.',
                'rating'  => 6,
            ])
            ->assertStatus(422);

        $this->actAsPatient($patient)
            ->postJson("/api/v1/trainings/{$training->id}/reviews", [
                'content' => 'This is a long enough review content.',
                'rating'  => 0,
            ])
            ->assertStatus(422);
    }

    public function test_new_review_is_pending_approval(): void
    {
        Notification::fake();
        $training = Training::factory()->create();
        $patient  = Patient::factory()->verified()->create();

        $this->actAsPatient($patient)
            ->postJson("/api/v1/trainings/{$training->id}/reviews", [
                'content' => 'Amazing training content and excellent instructor.',
                'rating'  => 5,
            ])
            ->assertCreated();

        $this->assertDatabaseHas('reviews', [
            'reviewable_id' => $training->id,
            'is_approved'   => 0,
        ]);
    }
}
