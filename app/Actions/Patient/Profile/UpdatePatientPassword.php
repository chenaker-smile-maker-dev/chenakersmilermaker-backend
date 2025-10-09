<?php

namespace App\Actions\Patient\Profile;

use App\Enums\Api\TokenAbility;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\NewAccessToken;

class UpdatePatientPassword
{
    /**
     * Handle password update for patient
     *
     * @param Patient $patient
     * @param array $data
     * @return NewAccessToken
     * @throws ValidationException
     */
    public function handle(Patient $patient, array $data): NewAccessToken
    {
        // Verify old password
        if (!Hash::check($data['old_password'], $patient->password)) {
            throw ValidationException::withMessages([
                'old_password' => ['The provided password does not match your current password.'],
            ]);
        }

        // Update password
        $patient->update([
            'password' => Hash::make($data['new_password']),
        ]);

        // Delete only access tokens (keep refresh token)
        $patient->tokens()->where('name', 'access_token')->delete();

        // Generate new access token
        $accessToken = $patient->createToken(
            'access_token',
            [TokenAbility::ACCESS_API->value],
            Carbon::now()->addMinutes(config('sanctum.access_token_expiration'))
        );

        return $accessToken;
    }
}
