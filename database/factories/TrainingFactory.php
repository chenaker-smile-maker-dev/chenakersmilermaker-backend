<?php

namespace Database\Factories;

use App\Models\Training;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrainingFactory extends Factory
{
    protected $model = Training::class;

    public function definition(): array
    {
        $titles = [
            'fr' => $this->faker->sentence(3), // French title
            'ar' => $this->faker->sentence(3), // Arabic title
            'en' => $this->faker->sentence(3), // English title
        ];

        return [
            'title' => $titles,
            'description' => [
                'fr' => $this->faker->paragraph(3), // French description
                'ar' => $this->faker->paragraph(3), // Arabic description
                'en' => $this->faker->paragraph(3), // English description
            ],
            'trainer_name' => $this->faker->name(),
            'duration' => $this->faker->randomElement(['2 hours', '4 hours', '1 day', '2 days', '1 week']),
            'documents' => [
                'intro.pdf' => 'https://example.com/intro.pdf',
                'slides.docx' => 'https://example.com/slides.docx',
            ],
            'video_url' => $this->faker->randomElement([
                'https://youtube.com/watch?v=example1',
                'https://youtube.com/watch?v=example2',
                null,
            ]),
            'deleted_at' => null,
        ];
    }

    /**
     * 1 in 10 chance of being soft deleted
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Training $training) {
            if ($this->faker->numberBetween(1, 10) === 1) {
                $training->update([
                    'deleted_at' => $this->faker->dateTimeBetween('-1 year', '-1 day'),
                ]);
            }
        });
    }
}
