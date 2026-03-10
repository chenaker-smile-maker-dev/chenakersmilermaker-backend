<?php

use App\Filament\Admin\Resources\Trainings\Pages\CreateTraining;
use App\Filament\Admin\Resources\Trainings\Pages\EditTraining;
use App\Filament\Admin\Resources\Trainings\Pages\ListTrainings;
use App\Filament\Admin\Resources\Trainings\Pages\ViewTraining;
use App\Filament\Admin\Resources\Trainings\RelationManagers\ReviewsRelationManager;
use App\Models\Review;
use App\Models\Training;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\Testing\TestAction;
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

it('can render the trainings list page', function () {
    livewire(ListTrainings::class)->assertOk();
});

it('shows training records in the table', function () {
    $trainings = Training::factory()->count(3)->create();

    livewire(ListTrainings::class)
        ->assertOk()
        ->assertCanSeeTableRecords($trainings);
});

it('can search trainings by title', function () {
    $training = Training::factory()->create([
        'title' => ['en' => 'Unique Implant Training', 'ar' => 'تدريب', 'fr' => 'Formation'],
    ]);
    Training::factory()->count(3)->create();

    livewire(ListTrainings::class)
        ->searchTable('Unique Implant Training')
        ->assertCanSeeTableRecords(collect([$training]));
});

it('can sort trainings by trainer_name', function () {
    Training::factory()->count(5)->create();

    $sortedAsc  = Training::query()->orderBy('trainer_name')->get();
    $sortedDesc = Training::query()->orderBy('trainer_name', 'desc')->get();

    livewire(ListTrainings::class)
        ->sortTable('trainer_name')
        ->assertCanSeeTableRecords($sortedAsc, inOrder: true)
        ->sortTable('trainer_name', 'desc')
        ->assertCanSeeTableRecords($sortedDesc, inOrder: true);
});

it('has the expected table columns', function () {
    livewire(ListTrainings::class)
        ->assertTableColumnExists('title')
        ->assertTableColumnExists('trainer_name')
        ->assertTableColumnExists('duration');
});

// ─── Create ────────────────────────────────────────────────────────────────────

it('can render the create training page', function () {
    livewire(CreateTraining::class)->assertOk();
});

it('can create a training', function () {
    livewire(CreateTraining::class)
        ->fillForm([
            'title'        => ['en' => 'New Training', 'ar' => 'تدريب', 'fr' => 'Formation'],
            'trainer_name' => 'Dr. Moussa',
            'duration'     => '2 hours',
            'price'        => 15000,
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertRedirect();

    assertDatabaseHas(Training::class, ['trainer_name' => 'Dr. Moussa']);
});

it('validates required fields when creating a training', function () {
    livewire(CreateTraining::class)
        ->fillForm(['title' => ['en' => null, 'ar' => null, 'fr' => null], 'trainer_name' => null, 'duration' => null])
        ->call('create')
        ->assertHasFormErrors([
            'title.en'     => 'required',
            'trainer_name' => 'required',
            'duration'     => 'required',
        ]);
});

// ─── Edit ──────────────────────────────────────────────────────────────────────

it('can render the edit training page with correct data', function () {
    $training = Training::factory()->create();

    livewire(EditTraining::class, ['record' => $training->id])
        ->assertOk()
        ->assertSchemaStateSet([
            'trainer_name' => $training->trainer_name,
            'price'        => $training->price,
        ]);
});

it('can update a training price', function () {
    $training = Training::factory()->create();

    livewire(EditTraining::class, ['record' => $training->id])
        ->fillForm(['price' => 12500])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();

    assertDatabaseHas(Training::class, ['id' => $training->id, 'price' => 12500]);
});

// ─── View ──────────────────────────────────────────────────────────────────────

it('can render the view training page', function () {
    $training = Training::factory()->create();

    livewire(ViewTraining::class, ['record' => $training->id])
        ->assertOk();
});

// ─── Reviews Relation Manager ──────────────────────────────────────────────────

it('can render the reviews relation manager on the edit page', function () {
    $training = Training::factory()->create();

    livewire(EditTraining::class, ['record' => $training->id])
        ->assertOk()
        ->assertSeeLivewire(ReviewsRelationManager::class);
});

it('reviews relation manager shows training reviews', function () {
    $training = Training::factory()->create();
    $reviews  = Review::factory()->count(3)->forTraining($training)->create();

    livewire(ReviewsRelationManager::class, [
        'ownerRecord' => $training,
        'pageClass'   => EditTraining::class,
    ])
        ->assertOk()
        ->assertCanSeeTableRecords($reviews);
});

it('can approve a review from the relation manager', function () {
    $training = Training::factory()->create();
    $review   = Review::factory()->forTraining($training)->create([
        'is_approved' => false,
    ]);

    livewire(ReviewsRelationManager::class, [
        'ownerRecord' => $training,
        'pageClass'   => EditTraining::class,
    ])
        ->callAction(TestAction::make('approve')->table($review))
        ->assertNotified();

    expect($review->fresh()->is_approved)->toBeTrue();
});

// ─── Delete ────────────────────────────────────────────────────────────────────

it('can soft-delete a training from the edit page', function () {
    $training = Training::factory()->create();

    livewire(EditTraining::class, ['record' => $training->id])
        ->callAction(DeleteAction::class)
        ->assertRedirect();

    assertSoftDeleted(Training::class, ['id' => $training->id]);
});
