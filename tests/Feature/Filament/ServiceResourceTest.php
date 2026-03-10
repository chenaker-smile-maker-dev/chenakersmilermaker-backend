<?php

use App\Enums\Service\ServiceAvailability;
use App\Filament\Admin\Resources\Services\Pages\CreateService;
use App\Filament\Admin\Resources\Services\Pages\EditService;
use App\Filament\Admin\Resources\Services\Pages\ListServices;
use App\Filament\Admin\Resources\Services\Pages\ViewService;
use App\Models\Service;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Livewire\livewire;

beforeEach(function () {
    actingAs(User::factory()->create());
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

// ─── List ──────────────────────────────────────────────────────────────────────

it('can render the services list page', function () {
    livewire(ListServices::class)->assertOk();
});

it('shows service records in the table', function () {
    $services = Service::factory()->count(3)->create();

    livewire(ListServices::class)
        ->assertOk()
        ->assertCanSeeTableRecords($services);
});

it('can search services by name', function () {
    $service = Service::factory()->create([
        'name' => ['en' => 'Unique Whitening', 'ar' => 'تبييض', 'fr' => 'Blanchiment'],
    ]);
    Service::factory()->count(3)->create();

    livewire(ListServices::class)
        ->searchTable('Unique Whitening')
        ->assertCanSeeTableRecords(collect([$service]));
});

it('has the expected table columns', function () {
    livewire(ListServices::class)
        ->assertTableColumnExists('name')
        ->assertTableColumnExists('price')
        ->assertTableColumnExists('availability');
});

// ─── Create ────────────────────────────────────────────────────────────────────

it('can render the create service page', function () {
    livewire(CreateService::class)->assertOk();
});

it('can create a service', function () {
    livewire(CreateService::class)
        ->fillForm([
            'name'         => ['en' => 'Tooth Cleaning', 'ar' => 'تنظيف', 'fr' => 'Nettoyage'],
            'price'        => 2500,
            'duration'     => 30,
            'availability' => ServiceAvailability::BOTH->value,
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertRedirect();

    assertDatabaseHas(Service::class, ['price' => 2500]);
});

it('validates required fields when creating a service', function () {
    livewire(CreateService::class)
        ->fillForm(['name' => ['en' => null, 'ar' => null, 'fr' => null], 'price' => null, 'duration' => null])
        ->call('create')
        ->assertHasFormErrors([
            'name.en'  => 'required',
            'price'    => 'required',
            'duration' => 'required',
        ]);
});

// ─── Edit ──────────────────────────────────────────────────────────────────────

it('can render the edit service page with correct data', function () {
    $service = Service::factory()->create();

    livewire(EditService::class, ['record' => $service->id])
        ->assertOk()
        ->assertSchemaStateSet(['price' => $service->price]);
});

it('can update a service price', function () {
    $service = Service::factory()->create();

    livewire(EditService::class, ['record' => $service->id])
        ->fillForm(['price' => 9999])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();

    assertDatabaseHas(Service::class, ['id' => $service->id, 'price' => 9999]);
});

// ─── View ──────────────────────────────────────────────────────────────────────

it('can render the view service page', function () {
    $service = Service::factory()->create();

    livewire(ViewService::class, ['record' => $service->id])
        ->assertOk();
});

// ─── Delete ────────────────────────────────────────────────────────────────────

it('can delete a service from the edit page', function () {
    $service = Service::factory()->create();

    livewire(EditService::class, ['record' => $service->id])
        ->callAction(DeleteAction::class)
        ->assertRedirect();

    assertDatabaseMissing(Service::class, ['id' => $service->id]);
});
