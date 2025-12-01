<?php

namespace Database\Factories;

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
        $slugs = [
            'fr' => \Str::slug($titles['fr']),
            'ar' => \Str::slug($titles['ar']),
            'en' => \Str::slug($titles['en']),
        ];
        return [
            'title' => $titles,
            'slug' => $slugs,
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
        ];
    }
}
