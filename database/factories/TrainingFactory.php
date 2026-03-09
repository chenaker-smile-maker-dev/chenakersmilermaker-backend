<?php

namespace Database\Factories;

use App\Models\Training;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrainingFactory extends Factory
{
    protected $model = Training::class;

    public function definition(): array
    {
        return [
            'title' => [
                'en' => $this->faker->sentence(3),
                'ar' => $this->faker->sentence(3),
                'fr' => $this->faker->sentence(3),
            ],
            'description' => [
                'en' => $this->faker->paragraph(2),
                'ar' => $this->faker->paragraph(2),
                'fr' => $this->faker->paragraph(2),
            ],
            'trainer_name' => $this->faker->name(),
            'duration'     => $this->faker->randomElement(['2 hours', '4 hours', '1 day', '2 days', '1 week']),
            'price'        => $this->faker->numberBetween(5000, 50000),
            'video_url'    => $this->faker->optional(0.5)->url(),
            'deleted_at'   => null,
        ];
    }
}
