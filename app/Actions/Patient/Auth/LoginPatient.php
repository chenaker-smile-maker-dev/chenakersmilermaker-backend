<?php

namespace App\Actions\Patient\Auth;

use App\Models\Patient;


class LoginPatient
{
    public function handle(array $data): ?Patient
    {
        $patient = Patient::where('email', $data['email'])->first();

        if ($patient && password_verify($data['password'], $patient->password)) {
            return $patient;
        }
        return null;
    }
}
