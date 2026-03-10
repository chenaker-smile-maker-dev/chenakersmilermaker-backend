<?php

use App\Models\Service;
use Tests\Browser\Core\FilamentPage;

// ─── List ─────────────────────────────────────────────────────────────────────

it('services list page loads', function () {
    $page = adminVisit(FilamentPage::services());

    $page->assertPathIs(FilamentPage::services());
});

it('services list shows service names', function () {
    Service::factory()->create([
        'name' => ['en' => 'Browser Test Cleaning', 'ar' => 'تنظيف', 'fr' => 'Nettoyage'],
    ]);

    $page = adminVisit(FilamentPage::services());

    $page->assertSee('Browser Test Cleaning');
});

// ─── Create page (load only) ──────────────────────────────────────────────────

it('create service page loads', function () {
    $page = adminVisit(FilamentPage::serviceCreate());

    $page->assertPathIs(FilamentPage::serviceCreate())
        ->assertPresent('form');
});

// ─── Edit page (load only) ────────────────────────────────────────────────────

it('edit service page loads', function () {
    $service = Service::factory()->create();

    $page = adminVisit(FilamentPage::serviceEdit($service->id));

    $page->assertPresent('form');
});
