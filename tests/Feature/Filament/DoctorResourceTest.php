<?php

use App\Filament\Admin\Resources\Doctors\Pages\CreateDoctor;
use App\Filament\Admin\Resources\Doctors\Pages\EditDoctor;
use App\Filament\Admin\Resources\Doctors\Pages\ListDoctors;
use App\Filament\Admin\Resources\Doctors\Pages\ViewDoctor;
use App\Models\Doctor;
use App\Models\Service;
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

it('can render the doctors list page', function () {
    livewire(ListDoctors::class)->assertOk();
});

it('shows doctor records in the table', function () {
    $doctors = Doctor::factory()->count(3)->create();

    livewire(ListDoctors::class)
        ->assertOk()
        ->assertCanSeeTableRecords($doctors);
});

it('can search doctors by name', function () {
    $doctor = Doctor::factory()->create([
        'name' => ['en' => 'Dr. Unique', 'ar' => 'Dr. Unique', 'fr' => 'Dr. Unique'],
    ]);
    Doctor::factory()->count(3)->create();

    livewire(ListDoctors::class)
        ->searchTable('Dr. Unique')
        ->assertCanSeeTableRecords(collect([$doctor]));
});

it('has the expected table columns', function () {
    livewire(ListDoctors::class)
        ->assertTableColumnExists('name')
        ->assertTableColumnExists('specialty');
});

// ─── Create ────────────────────────────────────────────────────────────────────

it('can render the create doctor page', function () {
    livewire(CreateDoctor::class)->assertOk();
});

it('can create a doctor', function () {
    livewire(CreateDoctor::class)
        ->fillForm([
            'name'      => ['en' => 'Dr. Test', 'ar' => 'دكتور', 'fr' => 'Dr. Test'],
            'specialty' => ['en' => 'Cardiology', 'ar' => 'قلبية', 'fr' => 'Cardiologie'],
            'diplomas'  => [],
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertRedirect();
});

it('validates required fields when creating a doctor', function () {
    livewire(CreateDoctor::class)
        ->fillForm(['name' => ['en' => null, 'ar' => null, 'fr' => null], 'specialty' => ['en' => null, 'ar' => null, 'fr' => null], 'diplomas' => []])
        ->call('create')
        ->assertHasFormErrors([
            'name.en'      => 'required',
            'specialty.en' => 'required',
        ]);
});

// ─── Edit ──────────────────────────────────────────────────────────────────────

it('can render the edit doctor page', function () {
    $doctor = Doctor::factory()->create();

    livewire(EditDoctor::class, ['record' => $doctor->id])
        ->assertOk();
});

it('can update a doctor name', function () {
    $doctor = Doctor::factory()->create();

    livewire(EditDoctor::class, ['record' => $doctor->id])
        ->fillForm([
            'name' => ['en' => 'Dr. Updated', 'ar' => 'دكتور محدث', 'fr' => 'Dr. Mis à jour'],
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();

    assertDatabaseHas(Doctor::class, ['id' => $doctor->id]);
});

// ─── View ──────────────────────────────────────────────────────────────────────

it('can render the view doctor page', function () {
    $doctor = Doctor::factory()->create();

    livewire(ViewDoctor::class, ['record' => $doctor->id])
        ->assertOk();
});

it('view doctor page shows assigned services in infolist', function () {
    $service = Service::factory()->create([
        'name' => ['en' => 'Dental Cleaning', 'ar' => 'تنظيف', 'fr' => 'Nettoyage'],
    ]);
    $doctor = Doctor::factory()->create();
    $doctor->services()->attach($service);

    livewire(ViewDoctor::class, ['record' => $doctor->id])
        ->assertOk()
        ->assertSee('Dental Cleaning');
});

// ─── Delete ────────────────────────────────────────────────────────────────────

it('can soft-delete a doctor from the view page', function () {
    $doctor = Doctor::factory()->create();

    livewire(ViewDoctor::class, ['record' => $doctor->id])
        ->callAction(DeleteAction::class)
        ->assertNotified();

    assertSoftDeleted(Doctor::class, ['id' => $doctor->id]);
});
