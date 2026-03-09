<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        return [
            'title' => [
                'en' => $this->faker->sentence(),
                'ar' => $this->faker->sentence(),
                'fr' => $this->faker->sentence(),
            ],
            'description' => [
                'en' => $this->faker->paragraph(),
                'ar' => $this->faker->paragraph(),
                'fr' => $this->faker->paragraph(),
            ],
            'date'        => $this->faker->dateTimeBetween('+1 day', '+6 months'),
            'time'        => $this->faker->time('H:i'),
            'is_archived' => false,
            'location' => [
                'en' => $this->faker->city(),
                'ar' => $this->faker->city(),
                'fr' => $this->faker->city(),
            ],
            'speakers' => [
                'en' => $this->faker->name(),
                'ar' => $this->faker->name(),
                'fr' => $this->faker->name(),
            ],
            'about_event' => [
                'en' => $this->faker->paragraph(),
                'ar' => $this->faker->paragraph(),
                'fr' => $this->faker->paragraph(),
            ],
            'what_to_expect' => [
                'en' => $this->faker->paragraph(),
                'ar' => $this->faker->paragraph(),
                'fr' => $this->faker->paragraph(),
            ],
            'deleted_at' => null,
        ];
    }

    public function future(): static
    {
        return $this->state([
            'date'        => \Carbon\Carbon::now()->addDays(rand(1, 30))->toDateString(),
            'is_archived' => false,
        ]);
    }

    public function archived(): static
    {
        return $this->state([
            'is_archived' => true,
            'date'        => \Carbon\Carbon::now()->subDays(rand(1, 30))->toDateString(),
        ]);
    }

    public function happening(): static
    {
        return $this->state([
            'date'        => \Carbon\Carbon::now()->toDateString(),
            'is_archived' => false,
        ]);
    }
}
