<?php

use App\Models\Patient;
use Tests\Browser\Core\AdminSession;
use Tests\Browser\Core\BrowserAssertions;
use Tests\Browser\Core\FilamentPage;

// ─── List page ────────────────────────────────────────────────────────────────

it('patients list page loads', function () {
    $page = adminVisit(FilamentPage::patients());

    $page->assertPathIs(FilamentPage::patients());
});

it('patients list page shows a table', function () {
    Patient::factory()->count(3)->create();

    $page = adminVisit(FilamentPage::patients());

    BrowserAssertions::assertTableHasRows($page);
});

it('patients list page shows patient name column', function () {
    $patient = Patient::factory()->create([
        'first_name' => 'ZiyadBrowserTest',
        'last_name'  => 'Testoni',
    ]);

    $page = adminVisit(FilamentPage::patients());

    $page->assertSee('ZiyadBrowserTest');
});

// ─── Search ───────────────────────────────────────────────────────────────────

it('patients table can be searched by name', function () {
    Patient::factory()->create(['first_name' => 'SearchablePatient', 'last_name' => 'Demo']);
    Patient::factory()->create(['first_name' => 'AnotherOne', 'last_name' => 'Different']);

    $page = adminVisit(FilamentPage::patients());

    $page->type('input[placeholder*="Search"]', 'SearchablePatient')
        ->assertSee('SearchablePatient')
        ->assertDontSee('AnotherOne');
});

// ─── Create ───────────────────────────────────────────────────────────────────

it('create patient page loads', function () {
    $page = adminVisit(FilamentPage::patientCreate());

    $page->assertPathIs(FilamentPage::patientCreate())
        ->assertPresent('form');
});

it('can create a new patient', function () {
    $page = adminVisit(FilamentPage::patientCreate());

    $page->type('input[name="first_name"]', 'NewBrowserPatient')
        ->type('input[name="last_name"]', 'TestSurname')
        ->type('input[name="email"]', 'newbrowserpatient@test.dz')
        ->type('input[name="phone"]', '0660000001')
        ->type('input[name="age"]', '30')
        ->press('Create');

    expect(\App\Models\Patient::where('email', 'newbrowserpatient@test.dz')->exists())->toBeTrue();
});

// ─── Edit ─────────────────────────────────────────────────────────────────────

it('edit patient page loads', function () {
    $patient = Patient::factory()->create();

    $page = adminVisit(FilamentPage::patientEdit($patient->id));

    $page->assertPathIs(FilamentPage::patientEdit($patient->id))
        ->assertPresent('form');
});

it('can edit a patient first name', function () {
    $patient = Patient::factory()->create(['first_name' => 'OriginalFirst']);

    $page = adminVisit(FilamentPage::patientEdit($patient->id));

    $page->clear('input[name="first_name"]')
        ->type('input[name="first_name"]', 'UpdatedFirst')
        ->press('Save');

    expect($patient->fresh()->first_name)->toBe('UpdatedFirst');
});

// ─── View ─────────────────────────────────────────────────────────────────────

it('patient view page shows patient details', function () {
    $patient = Patient::factory()->create(['first_name' => 'ViewablePatient', 'last_name' => 'Detail']);

    $page = adminVisit(FilamentPage::patient($patient->id));

    $page->assertSee('ViewablePatient');
});
