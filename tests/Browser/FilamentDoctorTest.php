<?php

use App\Models\Doctor;
use App\Models\Service;
use Tests\Browser\Core\FilamentPage;

// ─── List ─────────────────────────────────────────────────────────────────────

it('doctors list page loads', function () {
    $page = adminVisit(FilamentPage::doctors());

    $page->assertPathIs(FilamentPage::doctors());
});

it('doctors list page shows doctor names', function () {
    Doctor::factory()->create([
        'name' => ['en' => 'Dr. BrowserTest Visible', 'ar' => 'د. اختبار', 'fr' => 'Dr. Test'],
    ]);

    $page = adminVisit(FilamentPage::doctors());

    $page->assertSee('BrowserTest Visible');
});

// ─── Create page (load only) ──────────────────────────────────────────────────

it('create doctor page loads', function () {
    $page = adminVisit(FilamentPage::doctorCreate());

    $page->assertPathIs(FilamentPage::doctorCreate())
        ->assertPresent('form');
});

// ─── Edit page (load only) ────────────────────────────────────────────────────

it('edit doctor page loads', function () {
    $doctor = Doctor::factory()->create();

    $page = adminVisit(FilamentPage::doctorEdit($doctor->id));

    $page->assertPathIs(FilamentPage::doctorEdit($doctor->id))
        ->assertPresent('form');
});

// ─── View page ────────────────────────────────────────────────────────────────

it('doctor view page shows doctor details', function () {
    $doctor = Doctor::factory()->create([
        'name'  => ['en' => 'Dr. ViewDetails Test', 'ar' => 'د. اختبار', 'fr' => 'Dr. Test'],
        'email' => 'viewdoctor@test.dz',
    ]);

    $page = adminVisit(FilamentPage::doctor($doctor->id));

    $page->assertSee('ViewDetails Test');
});

it('doctor page shows the assigned services section', function () {
    $doctor  = Doctor::factory()->create();
    $service = Service::factory()->create();
    $doctor->services()->attach($service->id);

    $page = adminVisit(FilamentPage::doctor($doctor->id));

    $page->assertSee($service->getTranslation('name', 'en'));
});
