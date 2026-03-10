<?php

use App\Models\Patient;
use App\Models\Testimonial;
use Tests\Browser\Core\FilamentPage;

// ─── List ─────────────────────────────────────────────────────────────────────

it('testimonials list page loads', function () {
    $page = adminVisit(FilamentPage::testimonials());

    $page->assertPathIs(FilamentPage::testimonials());
});

it('testimonials list shows patient name column', function () {
    $patient = Patient::factory()->create([
        'first_name' => 'TestiPatient',
        'last_name'  => 'Browser',
    ]);
    Testimonial::factory()->create([
        'patient_id'   => $patient->id,
        'patient_name' => 'TestiPatient Browser',
        'is_published' => true,
        'deleted_at'   => null,
    ]);

    $page = adminVisit(FilamentPage::testimonials());

    $page->assertSee('TestiPatient Browser');
});

it('testimonials list shows published and unpublished rows', function () {
    Testimonial::factory()->create(['is_published' => true, 'deleted_at' => null]);
    Testimonial::factory()->create(['is_published' => false, 'deleted_at' => null]);

    $page = adminVisit(FilamentPage::testimonials());

    $page->assertPresent('.fi-ta-row');
});

it('testimonials list shows rating values', function () {
    Testimonial::factory()->create([
        'rating'       => 5,
        'patient_name' => 'RatingTestPatient',
        'deleted_at'   => null,
    ]);

    $page = adminVisit(FilamentPage::testimonials());

    $page->assertSee('RatingTestPatient');
});

// ─── Create page (load only) ──────────────────────────────────────────────────

it('create testimonial page loads', function () {
    $page = adminVisit(FilamentPage::testimonialCreate());

    $page->assertPresent('form');
});

// ─── Edit page (load only) ────────────────────────────────────────────────────

it('edit testimonial page loads', function () {
    $testimonial = Testimonial::factory()->create(['deleted_at' => null]);

    $page = adminVisit(FilamentPage::testimonialEdit($testimonial->id));

    $page->assertPresent('form');
});
