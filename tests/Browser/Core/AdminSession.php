<?php

namespace Tests\Browser\Core;

use App\Models\User;

/**
 * AdminSession — helpers for authenticating as an admin in browser tests.
 *
 * Usage:
 *   $page = AdminSession::login($this);
 *   $page->assertPathIs('/admin');
 */
class AdminSession
{
    /**
     * The default admin credentials seeded by DemoSeeder / CreateAdminUserSeeder.
     */
    public const EMAIL    = 'admin@clinic.dz';
    public const PASSWORD = 'password';

    /**
     * Authenticate as admin and navigate to the Filament panel.
     *
     * Uses actingAs() (server-side) to bypass the Livewire login form because
     * Playwright's fill() doesn't reliably trigger Alpine.js / Livewire v3
     * reactive binding, which causes the form to submit with empty credentials.
     */
    public static function login(mixed $test, ?string $email = null, ?string $password = null): mixed
    {
        $email    ??= self::EMAIL;
        $password ??= self::PASSWORD;

        // Ensure a user with these credentials exists in the database.
        $user = User::firstOrCreate(
            ['email' => $email],
            ['name' => 'Test Admin', 'password' => 'password']
        );

        // Authenticate via test helper so the Filament guard session is set.
        $test->actingAs($user, 'web');

        // Navigate directly to the admin panel (skip the login form).
        return $test->visit('/admin');
    }

    /**
     * Create a fresh admin user (unique per test to avoid state leakage).
     */
    public static function createFresh(string $suffix = ''): User
    {
        return User::factory()->create([
            'email'    => "admin-{$suffix}" . uniqid() . '@test.dz',
            'password' => bcrypt('password'),
        ]);
    }

    /**
     * Log in with a specific User model instance.
     */
    public static function loginAs(mixed $test, User $user): mixed
    {
        $test->actingAs($user, 'web');

        return $test->visit('/admin');
    }
}
