<?php

namespace Database\Factories;

use App\Models\Doctor;
use Illuminate\Database\Eloquent\Factories\Factory;

class DoctorFactory extends Factory
{
    protected $model = Doctor::class;

    public function definition(): array
    {
        return [
            'name' => [
                'fr' => $this->faker->name(), // French name
                'ar' => $this->faker->name(), // Arabic name (using Arabic locale)
                'en' => $this->faker->name(), // English name
            ],
            'specialty' => [
                'fr' => $this->faker->randomElement([
                    'Cardiologist',
                    'Dermatologist',
                    'Neurologist',
                    'Pediatrician',
                    'Orthopedic Surgeon',
                ]),
                'ar' => $this->faker->randomElement([
                    'طبيب قلب',
                    'طبيب أمراض جلدية',
                    'طبيب أعصاب',
                    'طبيب أطفال',
                    'جراح عظام',
                ]),
                'en' => $this->faker->randomElement([
                    'Cardiologist',
                    'Dermatologist',
                    'Neurologist',
                    'Pediatrician',
                    'Orthopedic Surgeon',
                ]),
            ],
            'diplomas' => array_map(function () {
                return $this->faker->sentence(3);
            }, range(1, $this->faker->numberBetween(1, 5))),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'metadata' => $this->generateRandomMetadata(),
        ];
    }

    private function generateRandomMetadata(): array
    {
        $metadata = [];
        $numberOfPairs = $this->faker->numberBetween(3, 5);

        for ($i = 0; $i < $numberOfPairs; $i++) {
            $key = $this->faker->unique()->word();
            $value = $this->faker->sentence(2);
            $metadata[$key] = $value;
        }

        return $metadata;
    }

    // public function configure()
    // {
    //     return $this->afterCreating(function (Doctor $doctor) {
    //         $doctor
    //             ->addMedia(public_path('images/profile-placeholder.png'))
    //             ->preservingOriginal()
    //             ->toMediaCollection('doctor_photo');
    //     });
    // }
}
