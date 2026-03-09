<?php

use App\Models\Service;
use Tests\Browser\Core\BrowserAssertions;
use Tests\Browser\Core\FilamentPage;

// ─── List ─────────────────────────────────────────────────────────────────────

it('services list page loads', function () {
    $page = adminVisit(FilamentPage::services());

    $page->assertPathIs(FilamentPage::services());
});

it('services list shows service names', function () {
    Service::factory()->create(['name' => ['en' => 'Browser Test Cleaning', 'ar' => 'تنظيف', 'fr' => 'Nettoyage']]);

    $page = adminVisit(FilamentPage::services());

    $page->assertSee('Browser Test Cleaning');
});

// ─── Search ───────────────────────────────────────────────────────────────────

it('can search services table', function () {
    Service::factory()->create(['name' => ['en' => 'SearchableService', 'ar' => 'خدمة', 'fr' => 'Service']]);
    Service::factory()->create(['name' => ['en' => 'OtherServiceName', 'ar' => 'أخرى', 'fr' => 'Autre']]);

    $page = adminVisit(FilamentPage::services());

    $page->type('input[placeholder*="Search"]', 'SearchableService')
        ->assertSee('SearchableService');
});

// ─── Create ───────────────────────────────────────────────────────────────────

it('create service page loads', function () {
    $page = adminVisit(FilamentPage::serviceCreate());

    $page->assertPathIs(FilamentPage::serviceCreate())
        ->assertPresent('form');
});

it('can create a new service', function () {
    $page = adminVisit(FilamentPage::serviceCreate());

    // Fill English name tab
    $page->type('input[name="name.en"]', 'New Browser Service EN')
        ->type('input[name="name.ar"]', 'خدمة اختبار')
        ->type('input[name="name.fr"]', 'Service Navigateur')
        ->type('input[name="price"]', '5000')
        ->press('Create');

    expect(Service::whereJsonContains('name->en', 'New Browser Service EN')->exists())->toBeTrue();
});

// ─── Edit ─────────────────────────────────────────────────────────────────────

it('edit service page loads', function () {
    $service = Service::factory()->create();

    $page = adminVisit(FilamentPage::serviceEdit($service->id));

    $page->assertPresent('form');
});
