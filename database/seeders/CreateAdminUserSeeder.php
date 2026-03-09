<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CreateAdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@clinic.dz'],
            [
                'name'     => 'Clinic Admin',
                'password' => Hash::make('password'),
            ]
        );

        // A second admin for testing role separation
        User::firstOrCreate(
            ['email' => 'admin@admin.dev'],
            [
                'name'     => 'Super Admin',
                'password' => Hash::make('password'),
            ]
        );
    }
}
