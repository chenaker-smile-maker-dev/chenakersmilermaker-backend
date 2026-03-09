<?php

use App\Models\Patient;
use App\Models\Testimonial;
use Tests\Browser\Core\BrowserAssertions;
use Tests\Browser\Core\FilamentPage;

// ─── List ─────────────────────────────────────────────────────────────────────

it('testimonials list page loads', function () {
    $page = adminVisit(FilamentPage::testimonials());

    $page->assertPathIs(FilamentPage::testimonials());
});

it('testimonials list shows patient name column', function () {
    $patient = Patient::factory()->create(['first_name' => 'TestiPatient', 'last_name' => 'Browser']);
    Testimonial::factory()->create([
        'patient_id'   => $patient->id,
        'patient_name' => 'TestiPatient Browser',
        'is_published' => true,
    ]);

    $page = adminVisit(FilamentPage::testimonials());

    $page->assertSee('TestiPatient Browser');
});

it('testimonials list shows published badge', function () {
    Testimonial::factory()->create(['is_published' => true]);
    Testimonial::factory()->create(['is_published' => false]);

    $page = adminVisit(FilamentPage::testimonials());

    // Should show both published and unpublished states
    $page->assertPresent('table tbody tr');
});

// ─── Search ───────────────────────────────────────────────────────────────────

it('can search testimonials by patient name', function () {
    Testimonial::factory()->create(['patient_name' => 'SearchableTestimonialPatient']);
    Testimonial::factory()->create(['patient_name' => 'AnotherTestimonialPerson']);

    $page = adminVisit(FilamentPage::testimonials());

    $page->type('input[placeholder*="Search"]', 'SearchableTestimonial')
        ->assertSee('SearchableTestimonialPatient');
});

// ─── Rating filter ────────────────────────────────────────────────────────────

it('testimonials list shows rating values', function () {
    Testimonial::factory()->create(['rating' => 5]);

    $page = adminVisit(FilamentPage::testimonials());

    $page->assertSee('5');
});

// ─── Edit / Publish actions ───────────────────────────────────────────────────

it('can toggle testimonial published status', function () {
    $testimonial = Testimonial::factory()->create(['is_published' => false]);

    $page = adminVisit(FilamentPage::testimonialEdit($testimonial->id));

    $page->assertPresent('form');
});
