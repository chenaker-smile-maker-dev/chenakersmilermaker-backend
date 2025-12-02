<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DefaultSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = \App\Models\User::factory()->create([
            'name' => 'admin',
            'email' => 'admin@admin.dev',
        ]);

        $patients = \App\Models\Patient::factory()->count(10)->create();
        $doctors = \App\Models\Doctor::factory()->count(10)->create();
        $services = \App\Models\Service::factory()->count(10)->create();
        $appointments = \App\Models\Appointment::factory()->count(50)->create();
        $this->AssignServicesToDoctors($doctors, $services);

        $events = \App\Models\Event::factory()->count(10)->create();
        $trainings = \App\Models\Training::factory()->count(5)->create();
        $testimonials = \App\Models\Testimonial::factory()->count(15)->create();
    }

    protected function AssignServicesToDoctors($doctors, $services)
    {
        foreach ($doctors as $doctor) {
            $doctor->services()->attach($services->random(rand(2, 5))->pluck('id')->toArray());
        }
    }
}
