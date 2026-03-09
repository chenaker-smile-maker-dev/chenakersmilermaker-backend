<?php

namespace Database\Factories;

use App\Enums\UrgentBookingStatus;
use App\Models\Patient;
use App\Models\UrgentBooking;
use Illuminate\Database\Eloquent\Factories\Factory;

class UrgentBookingFactory extends Factory
{
    protected $model = UrgentBooking::class;

    public function definition(): array
    {
        return [
            'patient_id'          => Patient::factory(),
            'patient_name'        => $this->faker->name(),
            'patient_phone'       => $this->faker->phoneNumber(),
            'patient_email'       => $this->faker->optional(0.7)->safeEmail(),
            'reason'              => $this->faker->sentence(10),
            'description'         => $this->faker->optional(0.6)->paragraph(),
            'status'              => UrgentBookingStatus::PENDING,
            'admin_notes'         => null,
            'assigned_doctor_id'  => null,
            'preferred_datetime'  => $this->faker->optional(0.5)->dateTimeBetween('+1 day', '+7 days'),
            'scheduled_datetime'  => null,
            'metadata'            => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(['status' => UrgentBookingStatus::PENDING]);
    }

    public function accepted(): static
    {
        return $this->state(['status' => UrgentBookingStatus::ACCEPTED]);
    }

    public function rejected(): static
    {
        return $this->state(['status' => UrgentBookingStatus::REJECTED]);
    }

    public function completed(): static
    {
        return $this->state(['status' => UrgentBookingStatus::COMPLETED]);
    }

    public function forPatient(Patient $patient): static
    {
        return $this->state([
            'patient_id'    => $patient->id,
            'patient_name'  => $patient->first_name . ' ' . $patient->last_name,
            'patient_email' => $patient->email,
        ]);
    }

    public function withoutPatient(): static
    {
        return $this->state(['patient_id' => null]);
    }
}
