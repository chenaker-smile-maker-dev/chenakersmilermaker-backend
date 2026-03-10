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

// Browser tests use a persistent file-based SQLite so the embedded Playwright
// HTTP server shares the same database as the test code (avoids the :memory:
// new-connection-per-access problem that makes test data invisible to the browser).
uses(TestCase::class, \Pest\Browser\Browsable::class)
    ->beforeEach(function () {
        $dbFile = base_path('database/browser.sqlite');

        if (! file_exists($dbFile)) {
            touch($dbFile);
        }

        // Point the sqlite connection at the persistent file.
        config([
            'database.connections.sqlite.database' => $dbFile,
            'database.default'                     => 'sqlite',
        ]);

        // Purge cached in-memory connection so the next query uses the file.
        \Illuminate\Support\Facades\DB::purge('sqlite');

        // Fresh schema for every test (gives us clean isolation without transactions).
        \Illuminate\Support\Facades\Artisan::call('migrate:fresh');
    })
    ->in('Browser');

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
 * Returns the page after successful redirect to /admin.
 */
function adminLogin(): mixed
{
    return AdminSession::login(test());
}

/**
 * Browser helper: navigate to a Filament resource page after login.
 * Uses navigate() (page.goto) on the already-authenticated Webpage.
 */
function adminVisit(string $path): mixed
{
    return adminLogin()->navigate($path);
}
