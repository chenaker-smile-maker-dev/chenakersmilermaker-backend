<?php

use App\Enums\Api\TokenAbility;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Browser\Core\AdminSession;
use Tests\Browser\Core\FilamentPage;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
| Feature tests:  TestCase + RefreshDatabase (isolated SQLite per test).
| Browser tests:  TestCase + Browsable (Playwright drives a real browser;
|                 DB state managed via DemoSeeder / CreateAdminUserSeeder).
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
| Global helper functions
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

/**
 * Browser helper: log in as admin and return the dashboard page.
 * Uses the fixed DemoSeeder credentials so no DB writes are needed.
 */
function adminLogin(): \Pest\Browser\Api\Webpage
{
    return AdminSession::login(test());
}

/**
 * Browser helper: navigate to a Filament resource page after login.
 */
function adminVisit(string $path): \Pest\Browser\Api\Webpage
{
    return adminLogin()->visit($path);
}
