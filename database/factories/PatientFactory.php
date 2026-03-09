<?php

namespace Database\Factories;

use App\Enums\Patient\Gender;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Patient>
 */
class PatientFactory extends Factory
{
    public function definition(): array
    {
        return [
            'email'             => $this->faker->unique()->safeEmail(),
            'phone'             => $this->faker->unique()->numerify('05########'),
            'first_name'        => $this->faker->firstName(),
            'last_name'         => $this->faker->lastName(),
            'age'               => $this->faker->numberBetween(18, 80),
            'password'          => 'password',
            'gender'            => $this->faker->randomElement([Gender::MALE, Gender::FEMALE]),
            'email_verified_at' => null,
        ];
    }

    /** Patient with verified email. */
    public function verified(): static
    {
        return $this->state(['email_verified_at' => now()]);
    }

    /** Patient with unverified email. */
    public function unverified(): static
    {
        return $this->state(['email_verified_at' => null]);
    }
}
