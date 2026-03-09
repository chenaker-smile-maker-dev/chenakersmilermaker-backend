<?php

use App\Models\Event;
use Tests\Browser\Core\BrowserAssertions;
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

it('events list shows status column', function () {
    Event::factory()->future()->create();
    Event::factory()->archived()->create();

    $page = adminVisit(FilamentPage::events());

    // Status column should show future / archive badges
    $page->assertSee('future')->orAssertSee('archive');
});

// ─── Search ───────────────────────────────────────────────────────────────────

it('can search events by title', function () {
    Event::factory()->create(['title' => ['en' => 'UniqueEventSearch', 'ar' => 'بحث', 'fr' => 'Recherche']]);
    Event::factory()->create(['title' => ['en' => 'AnotherEventName',  'ar' => 'آخر', 'fr' => 'Autre']]);

    $page = adminVisit(FilamentPage::events());

    $page->type('input[placeholder*="Search"]', 'UniqueEventSearch')
        ->assertSee('UniqueEventSearch');
});

// ─── Create ───────────────────────────────────────────────────────────────────

it('create event page loads', function () {
    $page = adminVisit(FilamentPage::eventCreate());

    $page->assertPresent('form');
});

it('can create a new event', function () {
    $page = adminVisit(FilamentPage::eventCreate());

    $page->type('input[name="title.en"]', 'New Browser Event EN')
        ->type('input[name="title.ar"]', 'حدث جديد')
        ->type('input[name="title.fr"]', 'Nouvel événement')
        ->type('input[name="date"]', now()->addDays(15)->format('Y-m-d'))
        ->press('Create');

    expect(Event::whereJsonContains('title->en', 'New Browser Event EN')->exists())->toBeTrue();
});

// ─── Edit ─────────────────────────────────────────────────────────────────────

it('edit event page loads', function () {
    $event = Event::factory()->create();

    $page = adminVisit(FilamentPage::eventEdit($event->id));

    $page->assertPresent('form');
});

it('can update an event title', function () {
    $event = Event::factory()->create([
        'title' => ['en' => 'Old Title EN', 'ar' => 'قديم', 'fr' => 'Ancien'],
    ]);

    $page = adminVisit(FilamentPage::eventEdit($event->id));

    $page->clear('input[name="title.en"]')
        ->type('input[name="title.en"]', 'Updated Browser Title')
        ->press('Save');

    expect($event->fresh()->getTranslation('title', 'en'))->toBe('Updated Browser Title');
});

// ─── View ─────────────────────────────────────────────────────────────────────

it('event view page shows event details', function () {
    $event = Event::factory()->create([
        'title'    => ['en' => 'ViewableEventTitle', 'ar' => 'عنوان', 'fr' => 'Titre'],
        'location' => ['en' => 'Algiers Convention Centre', 'ar' => 'الجزائر', 'fr' => 'Centre des congrès'],
    ]);

    $page = adminVisit(FilamentPage::event($event->id));

    $page->assertSee('ViewableEventTitle');
});
