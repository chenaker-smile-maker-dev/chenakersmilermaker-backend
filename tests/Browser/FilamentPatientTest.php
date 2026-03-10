<?php

use App\Models\Patient;
use Tests\Browser\Core\FilamentPage;

// ─── List page ────────────────────────────────────────────────────────────────

it('patients list page loads', function () {
    $page = adminVisit(FilamentPage::patients());

    $page->assertPathIs(FilamentPage::patients());
});

it('patients list page shows a table', function () {
    Patient::factory()->count(3)->create();

    $page = adminVisit(FilamentPage::patients());

    $page->assertPresent('.fi-ta-row');
});

it('patients list page shows patient name column', function () {
    Patient::factory()->create([
        'first_name' => 'ZiyadBrowserTest',
        'last_name'  => 'Testoni',
    ]);

    $page = adminVisit(FilamentPage::patients());

    $page->assertSee('ZiyadBrowserTest');
});

// ─── Create page (load only) ──────────────────────────────────────────────────

it('create patient page loads', function () {
    $page = adminVisit(FilamentPage::patientCreate());

    $page->assertPathIs(FilamentPage::patientCreate())
        ->assertPresent('form');
});

// ─── Edit page (load only) ────────────────────────────────────────────────────

it('edit patient page loads', function () {
    $patient = Patient::factory()->create();

    $page = adminVisit(FilamentPage::patientEdit($patient->id));

    $page->assertPathIs(FilamentPage::patientEdit($patient->id))
        ->assertPresent('form');
});

// ─── View page ────────────────────────────────────────────────────────────────

it('patient view page shows patient details', function () {
    $patient = Patient::factory()->create([
        'first_name' => 'ViewablePatient',
        'last_name'  => 'Detail',
    ]);

    $page = adminVisit(FilamentPage::patient($patient->id));

    $page->assertSee('ViewablePatient');
});
