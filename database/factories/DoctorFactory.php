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
            ],
            'diplomas' => ['a', 'b', 'c'],
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Doctor $doctor) {
            $doctor
                ->addMedia(public_path('images/profile-placeholder.png'))
                ->preservingOriginal()
                ->toMediaCollection('doctor_photo');
        });
    }
}
