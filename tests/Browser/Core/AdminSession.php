<?php

namespace Tests\Browser\Core;

use App\Models\User;
use Pest\Browser\Api\Webpage;

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
     * Create an admin user (if not exists) and navigate to the Filament panel.
     * Returns the page already sitting on /admin after successful login.
     */
    public static function login(\Tests\TestCase $test, ?string $email = null, ?string $password = null): Webpage
    {
        $email    ??= self::EMAIL;
        $password ??= self::PASSWORD;

        // Ensure a user with these credentials exists in the database.
        User::firstOrCreate(
            ['email' => $email],
            ['name' => 'Test Admin', 'password' => bcrypt($password)]
        );

        return $test->visit('/admin/login')
            ->type('email', $email)
            ->type('password', $password)
            ->press('Sign in');
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
    public static function loginAs(\Tests\TestCase $test, User $user, string $password = 'password'): Webpage
    {
        return $test->visit('/admin/login')
            ->type('email', $user->email)
            ->type('password', $password)
            ->press('Sign in');
    }
}
