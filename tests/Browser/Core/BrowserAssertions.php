<?php

namespace Tests\Browser\Core;

use Pest\Browser\Api\Webpage;

/**
 * BrowserAssertions — custom assertion helpers for dental clinic panel tests.
 *
 * All methods return the Webpage for fluent chaining.
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
    public static function assertOnPanel(Webpage $page): Webpage
    {
        return $page->assertPathBeginsWith('/admin')
            ->assertPresent('nav');
    }

    /**
     * Assert the Filament panel sidebar navigation is present.
     */
    public static function assertSidebarPresent(Webpage $page): Webpage
    {
        return $page->assertPresent('nav[aria-label]');
    }

    /**
     * Assert a Filament table has at least one data row (not empty state).
     */
    public static function assertTableHasRows(Webpage $page): Webpage
    {
        return $page->assertPresent('table tbody tr');
    }

    /**
     * Assert a Filament table shows the empty state message.
     */
    public static function assertTableEmpty(Webpage $page): Webpage
    {
        return $page->assertPresent('[data-empty-state]');
    }

    /**
     * Assert a Filament badge/status chip with the given text is visible.
     */
    public static function assertBadge(Webpage $page, string $text): Webpage
    {
        return $page->assertSee($text);
    }

    /**
     * Assert a Filament notification toast with the given text appeared.
     */
    public static function assertNotificationToast(Webpage $page, string $text): Webpage
    {
        return $page->assertSee($text);
    }

    /**
     * Assert the current URL path is exactly the given path.
     */
    public static function assertPath(Webpage $page, string $path): Webpage
    {
        return $page->assertPathIs($path);
    }

    /**
     * Assert the page redirected to login (unauthenticated).
     */
    public static function assertRedirectedToLogin(Webpage $page): Webpage
    {
        return $page->assertPathIs('/admin/login');
    }

    /**
     * Assert a create form page is loaded (has a Save/Create button).
     */
    public static function assertCreateFormLoaded(Webpage $page): Webpage
    {
        return $page->assertPresent('form');
    }

    /**
     * Assert a widget heading or card title is visible on the dashboard.
     */
    public static function assertWidgetVisible(Webpage $page, string $heading): Webpage
    {
        return $page->assertSee($heading);
    }
}
