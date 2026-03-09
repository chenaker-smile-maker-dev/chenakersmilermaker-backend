<?php

namespace Database\Factories;

use App\Enums\PatientNotificationType;
use App\Models\Patient;
use App\Models\PatientNotification;
use Illuminate\Database\Eloquent\Factories\Factory;

class PatientNotificationFactory extends Factory
{
    protected $model = PatientNotification::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement(PatientNotificationType::cases());

        return [
            'patient_id' => Patient::factory(),
            'type'       => $type->value,
            'title'      => [
                'en' => $this->faker->sentence(4),
                'ar' => $this->faker->sentence(4),
                'fr' => $this->faker->sentence(4),
            ],
            'body'       => [
                'en' => $this->faker->sentence(10),
                'ar' => $this->faker->sentence(10),
                'fr' => $this->faker->sentence(10),
            ],
            'data'       => null,
            'action_url' => null,
            'read_at'    => null,
        ];
    }

    public function read(): static
    {
        return $this->state(['read_at' => now()]);
    }

    public function unread(): static
    {
        return $this->state(['read_at' => null]);
    }
}
