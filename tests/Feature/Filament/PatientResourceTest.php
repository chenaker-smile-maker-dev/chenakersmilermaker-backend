<?php

use App\Filament\Admin\Resources\Patients\Pages\CreatePatient;
use App\Filament\Admin\Resources\Patients\Pages\EditPatient;
use App\Filament\Admin\Resources\Patients\Pages\ListPatients;
use App\Filament\Admin\Resources\Patients\Pages\ViewPatient;
use App\Models\Patient;
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

it('can render the patients list page', function () {
    livewire(ListPatients::class)->assertOk();
});

it('shows patient records in the table', function () {
    $patients = Patient::factory()->count(5)->create();

    livewire(ListPatients::class)
        ->assertOk()
        ->assertCanSeeTableRecords($patients);
});

it('can search patients by first name', function () {
    $patients = Patient::factory()->count(5)->create();
    $target   = $patients->first();

    livewire(ListPatients::class)
        ->searchTable($target->first_name)
        ->assertCanSeeTableRecords($patients->where('first_name', $target->first_name));
});

it('has the expected table columns', function () {
    livewire(ListPatients::class)
        ->assertTableColumnExists('full_name')
        ->assertTableColumnExists('email')
        ->assertTableColumnExists('phone');
});

// ─── Create ────────────────────────────────────────────────────────────────────

it('can render the create patient page', function () {
    livewire(CreatePatient::class)->assertOk();
});

it('can create a patient', function () {
    livewire(CreatePatient::class)
        ->fillForm([
            'first_name' => 'Alice',
            'last_name'  => 'Smith',
            'email'      => 'alice@clinic.dz',
            'phone'      => '0660000001',
            'password'   => 'password123',
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertRedirect();

    assertDatabaseHas(Patient::class, ['email' => 'alice@clinic.dz']);
});

it('validates required fields when creating a patient', function () {
    livewire(CreatePatient::class)
        ->fillForm(['first_name' => '', 'last_name' => '', 'email' => '', 'phone' => ''])
        ->call('create')
        ->assertHasFormErrors([
            'first_name' => 'required',
            'last_name'  => 'required',
            'email'      => 'required',
            'phone'      => 'required',
        ]);
});

it('validates email format when creating a patient', function () {
    livewire(CreatePatient::class)
        ->fillForm([
            'first_name' => 'Bob',
            'last_name'  => 'Jones',
            'email'      => 'not-an-email',
            'phone'      => '0660000002',
            'password'   => 'password123',
        ])
        ->call('create')
        ->assertHasFormErrors(['email' => 'email']);
});

// ─── Edit ──────────────────────────────────────────────────────────────────────

it('can render the edit patient page with pre-filled data', function () {
    $patient = Patient::factory()->create();

    livewire(EditPatient::class, ['record' => $patient->id])
        ->assertOk()
        ->assertSchemaStateSet([
            'first_name' => $patient->first_name,
            'last_name'  => $patient->last_name,
            'email'      => $patient->email,
        ]);
});

it('can update a patient record', function () {
    $patient = Patient::factory()->create();

    livewire(EditPatient::class, ['record' => $patient->id])
        ->fillForm(['first_name' => 'UpdatedName'])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();

    assertDatabaseHas(Patient::class, [
        'id'         => $patient->id,
        'first_name' => 'UpdatedName',
    ]);
});

// ─── View ──────────────────────────────────────────────────────────────────────

it('can render the view patient page', function () {
    $patient = Patient::factory()->create();

    livewire(ViewPatient::class, ['record' => $patient->id])
        ->assertOk()
        ->assertSchemaStateSet(['email' => $patient->email]);
});

// ─── Delete ────────────────────────────────────────────────────────────────────

it('can soft-delete a patient from the edit page', function () {
    $patient = Patient::factory()->create();

    livewire(EditPatient::class, ['record' => $patient->id])
        ->callAction(DeleteAction::class)
        ->assertRedirect();

    assertSoftDeleted(Patient::class, ['id' => $patient->id]);
});
