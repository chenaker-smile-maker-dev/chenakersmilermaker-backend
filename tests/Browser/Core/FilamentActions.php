<?php

namespace Tests\Browser\Core;

use Pest\Browser\Api\Webpage;

/**
 * FilamentActions — reusable helpers for common Filament UI interactions.
 *
 * All methods operate on a Webpage instance and return it for fluent chaining.
 *
 * Usage:
 *   $page = AdminSession::login($this);
 *   FilamentActions::openTableAction($page, 'Edit');
 */
class FilamentActions
{
    /**
     * Click a table row action button by its label text.
     * Works with Filament's dropdown row actions.
     */
    public static function openTableAction(Webpage $page, string $label): Webpage
    {
        // Open the actions dropdown for the first row, then click the action
        return $page->click('[data-action-name]')
            ->waitFor('[role="menuitem"]')
            ->clickText($label);
    }

    /**
     * Submit a Filament form (Save button).
     */
    public static function save(Webpage $page): Webpage
    {
        return $page->press('Save');
    }

    /**
     * Submit a Filament Create form.
     */
    public static function create(Webpage $page): Webpage
    {
        return $page->press('Create');
    }

    /**
     * Click the "New [Resource]" button on a list page.
     */
    public static function clickNew(Webpage $page): Webpage
    {
        return $page->click('[wire\\:click*="create"]');
    }

    /**
     * Confirm a Filament confirmation modal by pressing the confirm button.
     */
    public static function confirmModal(Webpage $page, string $buttonText = 'Confirm'): Webpage
    {
        return $page->waitFor('[role="dialog"]')
            ->press($buttonText);
    }

    /**
     * Fill a Filament text input by its name attribute.
     */
    public static function fillInput(Webpage $page, string $name, string $value): Webpage
    {
        return $page->type("input[name='{$name}']", $value);
    }

    /**
     * Select a Filament select option by its value.
     */
    public static function selectOption(Webpage $page, string $name, string $value): Webpage
    {
        return $page->select("select[name='{$name}']", $value);
    }

    /**
     * Search in a Filament table search input.
     */
    public static function searchTable(Webpage $page, string $query): Webpage
    {
        return $page->type('input[placeholder*="Search"]', $query);
    }

    /**
     * Click a Filament tab by its label.
     */
    public static function clickTab(Webpage $page, string $label): Webpage
    {
        return $page->clickText($label);
    }

    /**
     * Assert a Filament success notification is visible.
     */
    public static function assertSuccess(Webpage $page, string $text = 'success'): Webpage
    {
        return $page->assertSee($text);
    }

    /**
     * Navigate via sidebar link to a Filament resource.
     */
    public static function navigateTo(Webpage $page, string $label): Webpage
    {
        return $page->clickText($label);
    }
}
