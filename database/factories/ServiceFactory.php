<?php

namespace Database\Factories;

use App\Enums\Service\ServiceAvailability;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition(): array
    {
        return [
            'name' => [
                'fr' => $this->faker->word(),
                'ar' => $this->faker->word(),
                'en' => $this->faker->word(),
            ],
            'price' => $this->faker->numberBetween(500, 5000),
            'active' => $this->faker->boolean(),
            'availability' => $this->faker->randomElement(ServiceAvailability::cases()),
        ];
    }
}
