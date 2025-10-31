<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {

        User::factory()->create([
            'name' => 'admin',
            'email' => 'admin@admin.dev',
        ]);
        $patients = \App\Models\Patient::factory()->count(10)->create();
        $doctors = \App\Models\Doctor::factory()->count(10)->create();
        $services = \App\Models\Service::factory()->count(10)->create();
        foreach ($doctors as $doctor) $doctor->services()->attach($services->random(rand(2, 5))->pluck('id')->toArray());

        \App\Models\Appointment::factory()->count(50)->create();
    }
}
