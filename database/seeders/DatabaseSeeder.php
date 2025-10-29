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
        $doctors = \App\Models\Doctor::factory()->count(10)->create();
        $patients = \App\Models\Patient::factory()->count(10)->create();
        $services = \App\Models\Service::factory()->count(10)->create();

        // Create 50 appointments with relationships to the seeded data
        \App\Models\Appointment::factory()->count(50)->create();
    }
}
