<?php

use App\Models\Patient;
use App\Models\Review;
use App\Models\Training;
use Tests\Browser\Core\BrowserAssertions;
use Tests\Browser\Core\FilamentPage;

// ─── List ─────────────────────────────────────────────────────────────────────

it('trainings list page loads', function () {
    $page = adminVisit(FilamentPage::trainings());

    $page->assertPathIs(FilamentPage::trainings());
});

it('trainings list shows training titles', function () {
    Training::factory()->create([
        'title' => ['en' => 'Browser Visible Training', 'ar' => 'تدريب', 'fr' => 'Formation'],
    ]);

    $page = adminVisit(FilamentPage::trainings());

    $page->assertSee('Browser Visible Training');
});

it('trainings list shows price column', function () {
    Training::factory()->create(['price' => 25000]);

    $page = adminVisit(FilamentPage::trainings());

    // Price should be visible somewhere in the table
    $page->assertSee('25');
});

// ─── Search ───────────────────────────────────────────────────────────────────

it('can search trainings by title', function () {
    Training::factory()->create(['title' => ['en' => 'SearchableTraining', 'ar' => 'تدريب', 'fr' => 'Formation']]);
    Training::factory()->create(['title' => ['en' => 'OtherTrainingName',  'ar' => 'آخر',   'fr' => 'Autre']]);

    $page = adminVisit(FilamentPage::trainings());

    $page->type('input[placeholder*="Search"]', 'SearchableTraining')
        ->assertSee('SearchableTraining');
});

// ─── Create ───────────────────────────────────────────────────────────────────

it('create training page loads', function () {
    $page = adminVisit(FilamentPage::trainingCreate());

    $page->assertPresent('form');
});

it('can create a new training', function () {
    $page = adminVisit(FilamentPage::trainingCreate());

    $page->type('input[name="title.en"]', 'New Browser Training EN')
        ->type('input[name="title.ar"]', 'تدريب جديد')
        ->type('input[name="title.fr"]', 'Nouvelle formation')
        ->type('input[name="trainer_name"]', 'Dr. Browser Trainer')
        ->type('input[name="price"]', '20000')
        ->press('Create');

    expect(Training::whereJsonContains('title->en', 'New Browser Training EN')->exists())->toBeTrue();
});

// ─── Edit ─────────────────────────────────────────────────────────────────────

it('edit training page loads', function () {
    $training = Training::factory()->create();

    $page = adminVisit(FilamentPage::trainingEdit($training->id));

    $page->assertPresent('form');
});

it('can update training price', function () {
    $training = Training::factory()->create(['price' => 10000]);

    $page = adminVisit(FilamentPage::trainingEdit($training->id));

    $page->clear('input[name="price"]')
        ->type('input[name="price"]', '15000')
        ->press('Save');

    expect($training->fresh()->price)->toBe(15000);
});

// ─── View & Reviews relation manager ─────────────────────────────────────────

it('training view page shows training details', function () {
    $training = Training::factory()->create([
        'title'        => ['en' => 'ViewableTraining Title', 'ar' => 'عنوان', 'fr' => 'Titre'],
        'trainer_name' => 'Dr. ViewTest',
    ]);

    $page = adminVisit(FilamentPage::training($training->id));

    $page->assertSee('ViewableTraining Title')
        ->assertSee('Dr. ViewTest');
});

it('training view shows reviews tab', function () {
    $training = Training::factory()->create();
    $patient  = Patient::factory()->create();
    Review::factory()->create([
        'reviewable_type' => Training::class,
        'reviewable_id'   => $training->id,
        'patient_id'      => $patient->id,
        'rating'          => 5,
        'content'         => 'BrowserVisibleReviewContent',
        'is_approved'     => false,
    ]);

    $page = adminVisit(FilamentPage::training($training->id));

    // Reviews tab / relation manager section
    $page->assertSee('Reviews');
});
