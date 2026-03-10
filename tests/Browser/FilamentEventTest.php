<?php

use App\Models\Event;
use Tests\Browser\Core\FilamentPage;

// ─── List ─────────────────────────────────────────────────────────────────────

it('events list page loads', function () {
    $page = adminVisit(FilamentPage::events());

    $page->assertPathIs(FilamentPage::events());
});

it('events list shows event titles', function () {
    Event::factory()->create([
        'title' => ['en' => 'Browser Visible Event Title', 'ar' => 'عنوان', 'fr' => 'Titre'],
    ]);

    $page = adminVisit(FilamentPage::events());

    $page->assertSee('Browser Visible Event Title');
});

it('events list shows table rows', function () {
    Event::factory()->future()->create();
    Event::factory()->archived()->create();

    $page = adminVisit(FilamentPage::events());

    $page->assertPresent('.fi-ta-row');
});

// ─── Create page (load only) ──────────────────────────────────────────────────

it('create event page loads', function () {
    $page = adminVisit(FilamentPage::eventCreate());

    $page->assertPresent('form');
});

// ─── Edit page (load only) ────────────────────────────────────────────────────

it('edit event page loads', function () {
    $event = Event::factory()->create();

    $page = adminVisit(FilamentPage::eventEdit($event->id));

    $page->assertPresent('form');
});

// ─── View page ────────────────────────────────────────────────────────────────

it('event view page shows event details', function () {
    $event = Event::factory()->create([
        'title' => ['en' => 'ViewableEventTitle', 'ar' => 'عنوان', 'fr' => 'Titre'],
    ]);

    $page = adminVisit(FilamentPage::event($event->id));

    $page->assertSee('ViewableEventTitle');
});
