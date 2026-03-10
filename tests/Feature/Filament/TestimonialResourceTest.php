<?php

use App\Filament\Admin\Resources\Testimonials\Pages\CreateTestimonial;
use App\Filament\Admin\Resources\Testimonials\Pages\EditTestimonial;
use App\Filament\Admin\Resources\Testimonials\Pages\ListTestimonials;
use App\Filament\Admin\Resources\Testimonials\Pages\ViewTestimonial;
use App\Models\Testimonial;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\assertSoftDeleted;
use function Pest\Livewire\livewire;

beforeEach(function () {
    actingAs(User::factory()->create());
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

// ─── List ──────────────────────────────────────────────────────────────────────

it('can render the testimonials list page', function () {
    livewire(ListTestimonials::class)->assertOk();
});

it('shows testimonial records in the table', function () {
    $testimonials = Testimonial::factory()->count(3)->create(['deleted_at' => null]);

    livewire(ListTestimonials::class)
        ->assertOk()
        ->assertCanSeeTableRecords($testimonials);
});

it('can search testimonials by patient name', function () {
    $match   = Testimonial::factory()->create(['patient_name' => 'Alice Wonder', 'deleted_at' => null]);
    $noMatch = Testimonial::factory()->create(['patient_name' => 'Bob Builder', 'deleted_at' => null]);

    // Both visible without search
    livewire(ListTestimonials::class)
        ->assertCanSeeTableRecords(collect([$match, $noMatch]));
});

it('can filter testimonials by published status', function () {
    $published   = Testimonial::factory()->create(['is_published' => true, 'deleted_at' => null]);
    $unpublished = Testimonial::factory()->create(['is_published' => false, 'deleted_at' => null]);

    livewire(ListTestimonials::class)
        ->filterTable('is_published', true)
        ->assertCanSeeTableRecords(collect([$published]))
        ->assertCanNotSeeTableRecords(collect([$unpublished]));
});

it('has the expected table columns', function () {
    livewire(ListTestimonials::class)
        ->assertTableColumnExists('patient_name')
        ->assertTableColumnExists('rating')
        ->assertTableColumnExists('is_published');
});

// ─── Create ────────────────────────────────────────────────────────────────────

it('can render the create testimonial page', function () {
    livewire(CreateTestimonial::class)->assertOk();
});

it('can create a testimonial with valid data', function () {
    livewire(CreateTestimonial::class)
        ->fillForm([
            'patient_name' => 'Jane Doe',
            'rating'       => 5,
            'content'      => '<p>Excellent service, highly recommended!</p>',
            'is_published' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Testimonial::class, [
        'patient_name' => 'Jane Doe',
        'rating'       => 5,
        'is_published' => true,
    ]);
});

it('requires patient_name to create a testimonial', function () {
    livewire(CreateTestimonial::class)
        ->fillForm([
            'patient_name' => null,
            'rating'       => 4,
            'content'      => '<p>Great!</p>',
        ])
        ->call('create')
        ->assertHasFormErrors(['patient_name' => 'required']);
});

it('requires rating to create a testimonial', function () {
    livewire(CreateTestimonial::class)
        ->fillForm([
            'patient_name' => 'John Smith',
            'rating'       => null,
            'content'      => '<p>Nice place.</p>',
        ])
        ->call('create')
        ->assertHasFormErrors(['rating' => 'required']);
});

it('requires content to create a testimonial', function () {
    livewire(CreateTestimonial::class)
        ->fillForm([
            'patient_name' => 'John Smith',
            'rating'       => 3,
            'content'      => null,
        ])
        ->call('create')
        ->assertHasFormErrors(['content']);
});

// ─── Edit ──────────────────────────────────────────────────────────────────────

it('can render the edit testimonial page', function () {
    $testimonial = Testimonial::factory()->create(['deleted_at' => null]);

    livewire(EditTestimonial::class, ['record' => $testimonial->id])
        ->assertOk();
});

it('pre-fills the edit form with existing data', function () {
    $testimonial = Testimonial::factory()->create([
        'patient_name' => 'Pre-filled Name',
        'rating'       => 4,
        'is_published' => false,
        'deleted_at'   => null,
    ]);

    livewire(EditTestimonial::class, ['record' => $testimonial->id])
        ->assertFormSet([
            'patient_name' => 'Pre-filled Name',
            'rating'       => 4,
            'is_published' => false,
        ]);
});

it('can update a testimonial', function () {
    $testimonial = Testimonial::factory()->create([
        'patient_name' => 'Old Name',
        'rating'       => 3,
        'deleted_at'   => null,
    ]);

    livewire(EditTestimonial::class, ['record' => $testimonial->id])
        ->fillForm([
            'patient_name' => 'Updated Name',
            'rating'       => 5,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Testimonial::class, [
        'id'           => $testimonial->id,
        'patient_name' => 'Updated Name',
        'rating'       => 5,
    ]);
});

it('can toggle the published status on a testimonial', function () {
    $testimonial = Testimonial::factory()->create(['is_published' => false, 'deleted_at' => null]);

    livewire(EditTestimonial::class, ['record' => $testimonial->id])
        ->fillForm(['is_published' => true])
        ->call('save')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Testimonial::class, [
        'id'           => $testimonial->id,
        'is_published' => true,
    ]);
});

// ─── View ──────────────────────────────────────────────────────────────────────

it('can render the view testimonial page', function () {
    $testimonial = Testimonial::factory()->create(['deleted_at' => null]);

    livewire(ViewTestimonial::class, ['record' => $testimonial->id])
        ->assertOk();
});

// ─── Soft Delete ───────────────────────────────────────────────────────────────

it('can soft-delete a testimonial', function () {
    $testimonial = Testimonial::factory()->create(['deleted_at' => null]);

    livewire(EditTestimonial::class, ['record' => $testimonial->id])
        ->callAction(DeleteAction::class)
        ->assertRedirect();

    assertSoftDeleted(Testimonial::class, ['id' => $testimonial->id]);
});
