<?php

namespace App\Actions\Patient\Auth;

use App\Models\Patient;


class RegisterPatient
{
    public function handle(array $data): Patient
    {
        return Patient::query()->create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone' => $data['phone'],
            'email' => $data['email'],
            'age' => $data['age'],
            'gender' => $data['gender'],
            'password' => bcrypt($data['password']),
        ]);
    }
}
