<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Service;
use App\Models\Patient;
use App\Enums\Appointment\AppointmentStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition(): array
    {
        $from = $this->faker->dateTimeBetween('+1 day', '+30 days');
        $to = \DateTime::createFromFormat(
            'Y-m-d H:i:s',
            $from->format('Y-m-d H:i:s')
        )->modify('+1 hour');

        return [
            'from' => $from,
            'to' => $to,
            'doctor_id' => Doctor::inRandomOrder()->first()?->id ?? Doctor::factory(),
            'service_id' => Service::inRandomOrder()->first()?->id ?? Service::factory(),
            'patient_id' => Patient::inRandomOrder()->first()?->id ?? Patient::factory(),
            'price' => $this->faker->numberBetween(5000, 50000),
            'status' => $this->faker->randomElement(AppointmentStatus::cases()),
            'metadata' => [
                'notes' => $this->faker->optional(0.5)->sentence(),
                'duration_minutes' => 60,
            ],
        ];
    }
}
