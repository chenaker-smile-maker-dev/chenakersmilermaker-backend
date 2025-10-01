<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {

        // User::factory()->create([
        //     'name' => 'admin',
        //     'email' => 'admin@admin.dev',
        // ]);
        $doctor = \App\Models\Doctor::factory()->count(10)->create();
        // \App\Models\Reservation::factory()->count(50)->create();
    }
}
