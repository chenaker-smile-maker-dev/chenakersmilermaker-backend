<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $titles = [
            'fr' => $this->faker->sentence(), // French title
            'ar' => $this->faker->sentence(), // Arabic title (using Arabic locale)
            'en' => $this->faker->sentence(), // English title
        ];
        return [
            'title' => $titles,
            'description' => [
                'fr' => $this->faker->paragraph(), // French description
                'ar' => $this->faker->paragraph(), // Arabic description (using Arabic locale)
                'en' => $this->faker->paragraph(), // English description
            ],
            'date' => $this->faker->dateTimeBetween('+1 days', '+1 year'),
            'is_archived' => $this->faker->boolean(3),
            "location" => [
                'fr' => $this->faker->address(), // French location
                'ar' => $this->faker->address(), // Arabic location (using Arabic locale)
                'en' => $this->faker->address(), // English location
            ],
            'deleted_at' => null,
        ];
    }

    /**
     * 1 in 10 chance of being soft deleted
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Event $event) {
            if ($this->faker->numberBetween(1, 10) === 1) {
                $event->update([
                    'deleted_at' => $this->faker->dateTimeBetween('-1 year', '-1 day'),
                ]);
            }
        });
    }
}
