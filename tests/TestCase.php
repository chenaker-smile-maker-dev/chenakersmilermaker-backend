<?php

namespace Tests;

use App\Enums\Api\TokenAbility;
use App\Models\Patient;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Authenticate as a patient by issuing a real Sanctum access token.
     * This satisfies both auth:sanctum and the 'access' ability middleware.
     */
    protected function actAsPatient(Patient $patient): static
    {
        $token = $patient->createToken('test-token', [TokenAbility::ACCESS_API->value]);

        return $this->withToken($token->plainTextToken);
    }
}
