<?php

use App\Enums\Api\TokenAbility;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
| Feature tests: use TestCase + RefreshDatabase by default.
| Browser tests: TestCase only (Playwright manages its own DB state via seeders).
*/

uses(TestCase::class, RefreshDatabase::class)->in('Feature');
uses(TestCase::class, \Pest\Browser\Browsable::class)->in('Browser');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
*/

expect()->extend('toBeMultilang', function () {
    return $this->toBeArray()
        ->toHaveKeys(['en', 'ar', 'fr']);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
*/

/**
 * Authenticate the next request as a verified patient with a real Sanctum token.
 */
function actAsPatient(Patient $patient): Tests\TestCase
{
    /** @var Tests\TestCase $test */
    $test = test();
    $token = $patient->createToken('test-token', [TokenAbility::ACCESS_API->value]);
    return $test->withToken($token->plainTextToken);
}

/**
 * Build multilang assertion structure: ensures en/ar/fr keys exist.
 */
function multilangStructure(): array
{
    return ['en', 'ar', 'fr'];
}
