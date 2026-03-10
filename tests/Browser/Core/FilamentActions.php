<?php

namespace Tests\Browser\Core;

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
    public static function openTableAction(mixed $page, string $label): mixed
    {
        // Open the actions dropdown for the first row, then click the action
        return $page->click('[data-action-name]')
            ->wait(1)
            ->click($label);
    }

    /**
     * Submit a Filament form (Save button).
     */
    public static function save(mixed $page): mixed
    {
        return $page->press('Save');
    }

    /**
     * Submit a Filament Create form.
     */
    public static function create(mixed $page): mixed
    {
        return $page->press('Create');
    }

    /**
     * Click the "New [Resource]" button on a list page.
     */
    public static function clickNew(mixed $page): mixed
    {
        return $page->click('[wire\\:click*="create"]');
    }

    /**
     * Confirm a Filament confirmation modal by pressing the confirm button.
     */
    public static function confirmModal(mixed $page, string $buttonText = 'Confirm'): mixed
    {
        return $page->wait(2)
            ->press($buttonText);
    }

    /**
     * Fill a Filament text input by its name attribute.
     */
    public static function fillInput(mixed $page, string $name, string $value): mixed
    {
        return $page->type("input[name='{$name}']", $value);
    }

    /**
     * Select a Filament select option by its value.
     */
    public static function selectOption(mixed $page, string $name, string $value): mixed
    {
        return $page->select("select[name='{$name}']", $value);
    }

    /**
     * Search in a Filament table search input.
     */
    public static function searchTable(mixed $page, string $query): mixed
    {
        return $page->type('.fi-ta-search-field input', $query);
    }

    /**
     * Click a Filament tab by its label.
     */
    public static function clickTab(mixed $page, string $label): mixed
    {
        return $page->click($label);
    }

    /**
     * Assert a Filament success notification is visible.
     */
    public static function assertSuccess(mixed $page, string $text = 'success'): mixed
    {
        return $page->assertSee($text);
    }

    /**
     * Navigate via sidebar link to a Filament resource.
     */
    public static function navigateTo(mixed $page, string $label): mixed
    {
        return $page->click($label);
    }
}
