<?php

namespace App\Actions\Patient\Auth;

use App\Enums\Api\TokenAbility;
use App\Models\Patient;
use Carbon\Carbon;

class GenerateTokensForPatient
{
    public function handle(Patient $patient): array
    {
        $patient->tokens()->delete();
        $accessToken = $patient->createToken('access_token', [TokenAbility::ACCESS_API->value], Carbon::now()->addMinutes(config('sanctum.access_token_expiration')));
        $refreshToken = $patient->createToken('refresh_token', [TokenAbility::REFRESH_ACCESS_TOKEN->value], Carbon::now()->addMinutes(config('sanctum.refresh_token_expiration')));

        return [$accessToken, $refreshToken];
    }
}
