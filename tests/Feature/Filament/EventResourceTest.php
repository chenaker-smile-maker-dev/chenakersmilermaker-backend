<?php

use App\Filament\Admin\Resources\Events\Pages\CreateEvent;
use App\Filament\Admin\Resources\Events\Pages\EditEvent;
use App\Filament\Admin\Resources\Events\Pages\ListEvents;
use App\Filament\Admin\Resources\Events\Pages\ViewEvent;
use App\Models\Event;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertSoftDeleted;
use function Pest\Livewire\livewire;

beforeEach(function () {
    actingAs(User::factory()->create());
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

// ─── List ──────────────────────────────────────────────────────────────────────

it('can render the events list page', function () {
    livewire(ListEvents::class)->assertOk();
});

it('shows event records in the table', function () {
    $events = Event::factory()->count(3)->create();

    livewire(ListEvents::class)
        ->assertOk()
        ->assertCanSeeTableRecords($events);
});

it('can search events by title', function () {
    $event = Event::factory()->create([
        'title' => ['en' => 'Unique Dental Summit', 'ar' => 'قمة', 'fr' => 'Sommet'],
    ]);
    Event::factory()->count(3)->create();

    livewire(ListEvents::class)
        ->searchTable('Unique Dental Summit')
        ->assertCanSeeTableRecords(collect([$event]));
});

it('can filter events by archived status', function () {
    $active   = Event::factory()->count(2)->create(['is_archived' => false]);
    $archived = Event::factory()->count(2)->create(['is_archived' => true]);

    livewire(ListEvents::class)
        ->set('activeTab', 'archive')
        ->assertCanSeeTableRecords($archived)
        ->assertCanNotSeeTableRecords($active);
});

it('has the expected table columns', function () {
    livewire(ListEvents::class)
        ->assertTableColumnExists('title')
        ->assertTableColumnExists('date')
        ->assertTableColumnExists('is_archived');
});

// ─── Create ────────────────────────────────────────────────────────────────────

it('can render the create event page', function () {
    livewire(CreateEvent::class)->assertOk();
});

it('can create an event', function () {
    livewire(CreateEvent::class)
        ->fillForm([
            'title' => ['en' => 'Health Conference', 'ar' => 'مؤتمر', 'fr' => 'Conférence'],
            'date'  => '2026-06-15',
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertRedirect();

    assertDatabaseHas(Event::class, ['title->en' => 'Health Conference']);
});

it('validates required fields when creating an event', function () {
    livewire(CreateEvent::class)
        ->fillForm(['title' => ['en' => null, 'ar' => null, 'fr' => null], 'date' => null])
        ->call('create')
        ->assertHasFormErrors(['title.en' => 'required', 'date' => 'required']);
});

// ─── Edit ──────────────────────────────────────────────────────────────────────

it('can render the edit event page with correct data', function () {
    $event = Event::factory()->create();

    livewire(EditEvent::class, ['record' => $event->id])
        ->assertOk()
        ->assertSchemaStateSet(['date' => $event->date->toDateString()]);
});

it('can update an event', function () {
    $event = Event::factory()->create();

    livewire(EditEvent::class, ['record' => $event->id])
        ->fillForm([
            'title' => ['en' => 'Updated Conference', 'ar' => 'محدث', 'fr' => 'Mis à jour'],
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();
});

// ─── View ──────────────────────────────────────────────────────────────────────

it('can render the view event page', function () {
    $event = Event::factory()->create();

    livewire(ViewEvent::class, ['record' => $event->id])
        ->assertOk();
});

// ─── Delete ────────────────────────────────────────────────────────────────────

it('can soft-delete an event from the edit page', function () {
    $event = Event::factory()->create();

    livewire(EditEvent::class, ['record' => $event->id])
        ->callAction(DeleteAction::class)
        ->assertRedirect();

    assertSoftDeleted(Event::class, ['id' => $event->id]);
});
