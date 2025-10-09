<?php

namespace App\Actions\Patient\Profile;

use App\Models\Patient;
use Illuminate\Http\UploadedFile;

class EditPatientProfile
{
    public function handle(Patient $patient, array $data): Patient
    {
        // Update patient basic information
        $patient->update([
            'first_name' => $data['first_name'] ?? $patient->first_name,
            'last_name' => $data['last_name'] ?? $patient->last_name,
            'phone' => $data['phone'] ?? $patient->phone,
        ]);

        // Handle profile photo upload if provided
        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            $patient->addMedia($data['image'])
                ->toMediaCollection('profile_photo');
        }

        return $patient->fresh();
    }
}
