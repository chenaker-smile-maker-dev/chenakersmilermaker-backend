<?php

namespace Database\Factories;

use App\Models\Reservation;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReservationFactory extends Factory
{
    protected $model = Reservation::class;

    public function definition(): array
    {
        $from = $this->faker->dateTimeBetween('now', '+12 week');
        $to = (clone $from)->modify('+1 hours');

        return [
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'from' => $from,
            'to' => $to,
        ];
    }
}
