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
        $patients = \App\Models\Patient::factory()->count(10)->create();
        $doctors = \App\Models\Doctor::factory()->count(10)->create();
        $services = \App\Models\Service::factory()->count(10)->create();
        // $appointments = \App\Models\Appointment::factory()->count(50)->create();

        $admin = \App\Models\User::factory()->create([
            'name' => 'admin',
            'email' => 'admin@admin.dev',
        ]);

        $this->AssignServicesToDoctors($doctors, $services);
    }

    protected function AssignServicesToDoctors($doctors, $services)
    {
        foreach ($doctors as $doctor) {
            $doctor->services()->attach($services->random(rand(2, 5))->pluck('id')->toArray());
        }
    }
}
