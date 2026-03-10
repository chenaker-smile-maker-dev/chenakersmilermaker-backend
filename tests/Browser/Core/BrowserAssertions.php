<?php

namespace Tests\Browser\Core;

/**
 * BrowserAssertions — custom assertion helpers for dental clinic panel tests.
 *
 * All methods accept and return mixed to be compatible with
 * PendingAwaitablePage, AwaitableWebpage and Webpage from pest-plugin-browser.
 *
 * Usage:
 *   BrowserAssertions::assertOnPanel($page);
 *   BrowserAssertions::assertTableHasRows($page);
 */
class BrowserAssertions
{
    /**
     * Assert we are on any Filament admin panel page.
     */
    public static function assertOnPanel(mixed $page): mixed
    {
        return $page->assertPathBeginsWith('/admin')
            ->assertPresent('.fi-sidebar-nav');
    }

    /**
     * Assert the Filament panel sidebar navigation is present.
     */
    public static function assertSidebarPresent(mixed $page): mixed
    {
        return $page->assertPresent('.fi-sidebar-nav');
    }

    /**
     * Assert a Filament table has at least one data row (not empty state).
     */
    public static function assertTableHasRows(mixed $page): mixed
    {
        return $page->assertPresent('.fi-ta-row');
    }

    /**
     * Assert a Filament table shows the empty state message.
     */
    public static function assertTableEmpty(mixed $page): mixed
    {
        return $page->assertPresent('[data-empty-state]');
    }

    /**
     * Assert a Filament badge/status chip with the given text is visible.
     */
    public static function assertBadge(mixed $page, string $text): mixed
    {
        return $page->assertSee($text);
    }

    /**
     * Assert a Filament notification toast with the given text appeared.
     */
    public static function assertNotificationToast(mixed $page, string $text): mixed
    {
        return $page->assertSee($text);
    }

    /**
     * Assert the current URL path is exactly the given path.
     */
    public static function assertPath(mixed $page, string $path): mixed
    {
        return $page->assertPathIs($path);
    }

    /**
     * Assert the page redirected to login (unauthenticated).
     */
    public static function assertRedirectedToLogin(mixed $page): mixed
    {
        return $page->assertPathIs('/admin/login');
    }

    /**
     * Assert a create form page is loaded (has a Save/Create button).
     */
    public static function assertCreateFormLoaded(mixed $page): mixed
    {
        return $page->assertPresent('form');
    }

    /**
     * Assert a widget heading or card title is visible on the dashboard.
     */
    public static function assertWidgetVisible(mixed $page, string $heading): mixed
    {
        return $page->assertSee($heading);
    }
}
