<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Testimonial>
 */
class TestimonialFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'patient_id' => \App\Models\Patient::factory(),
            'patient_name' => $this->faker->name(),
            'content' => $this->faker->paragraph(4),
            'rating' => $this->faker->numberBetween(1, 5),
            'is_published' => $this->faker->boolean(80),
            'deleted_at' => rand(1, 10) === 1 ? $this->faker->dateTimeBetween('-1 year', '-1 day') : null,
        ];
    }
}
