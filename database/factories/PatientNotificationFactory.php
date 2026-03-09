<?php

namespace Database\Factories;

use App\Enums\PatientNotificationType;
use App\Models\Patient;
use App\Notifications\Patient\PatientGenericNotification;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Notifications\DatabaseNotification;

/**
 * Factory for Laravel's standard DatabaseNotification (notifications table).
 * Target: \Illuminate\Notifications\DatabaseNotification
 */
class PatientNotificationFactory extends Factory
{
    protected $model = DatabaseNotification::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement(PatientNotificationType::cases());

        return [
            'id'               => $this->faker->uuid(),
            'type'             => PatientGenericNotification::class,
            'notifiable_type'  => Patient::class,
            'notifiable_id'    => Patient::factory(),
            'data'             => [
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
            ],
            'read_at'          => null,
        ];
    }

    public function forPatient(Patient $patient): static
    {
        return $this->state([
            'notifiable_type' => Patient::class,
            'notifiable_id'   => $patient->id,
        ]);
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
