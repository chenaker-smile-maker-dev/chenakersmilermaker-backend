<?php

use App\Models\Patient;
use App\Models\Review;
use App\Models\Training;
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

it('trainings list shows trainer name column', function () {
    Training::factory()->create(['trainer_name' => 'BrowserTestTrainer']);

    $page = adminVisit(FilamentPage::trainings());

    $page->assertSee('BrowserTestTrainer');
});

// ─── Create page (load only) ──────────────────────────────────────────────────

it('create training page loads', function () {
    $page = adminVisit(FilamentPage::trainingCreate());

    $page->assertPresent('form');
});

// ─── Edit page (load only) ────────────────────────────────────────────────────

it('edit training page loads', function () {
    $training = Training::factory()->create();

    $page = adminVisit(FilamentPage::trainingEdit($training->id));

    $page->assertPresent('form');
});

// ─── View page ────────────────────────────────────────────────────────────────

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
    Review::factory()->forTraining($training)->create([
        'patient_id'  => $patient->id,
        'rating'      => 5,
        'content'     => 'BrowserVisibleReviewContent',
        'is_approved' => false,
    ]);

    $page = adminVisit(FilamentPage::training($training->id));

    $page->assertSee('Reviews');
});
