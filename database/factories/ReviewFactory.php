<?php

namespace Database\Factories;

use App\Models\Patient;
use App\Models\Review;
use App\Models\Training;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition(): array
    {
        return [
            'reviewable_type' => Training::class,
            'reviewable_id'   => Training::factory(),
            'patient_id'      => Patient::factory(),
            'reviewer_name'   => $this->faker->name(),
            'content'         => $this->faker->paragraph(2),
            'rating'          => $this->faker->numberBetween(1, 5),
            'is_approved'     => false,
        ];
    }

    public function approved(): static
    {
        return $this->state(['is_approved' => true]);
    }

    public function pending(): static
    {
        return $this->state(['is_approved' => false]);
    }

    public function forTraining(Training $training): static
    {
        return $this->state([
            'reviewable_type' => Training::class,
            'reviewable_id'   => $training->id,
        ]);
    }
}
