<?php

namespace Tests\Browser\Core;

/**
 * FilamentPage — URL constants and navigation helpers for all Filament resources.
 *
 * Usage:
 *   $page->visit(FilamentPage::patients());
 *   $page->visit(FilamentPage::patientCreate());
 */
class FilamentPage
{
    // ─── Base ─────────────────────────────────────────────────────────────────

    public static function base(): string               { return '/admin'; }
    public static function login(): string              { return '/admin/login'; }

    // ─── Appointments ─────────────────────────────────────────────────────────

    public static function appointments(): string       { return '/admin/appointments'; }
    public static function appointmentCreate(): string  { return '/admin/appointments/create'; }
    public static function appointment(int $id): string { return "/admin/appointments/{$id}"; }
    public static function appointmentEdit(int $id): string { return "/admin/appointments/{$id}/edit"; }

    // ─── Doctors ──────────────────────────────────────────────────────────────

    public static function doctors(): string            { return '/admin/doctors'; }
    public static function doctorCreate(): string       { return '/admin/doctors/create'; }
    public static function doctor(int $id): string      { return "/admin/doctors/{$id}"; }
    public static function doctorEdit(int $id): string  { return "/admin/doctors/{$id}/edit"; }

    // ─── Patients ─────────────────────────────────────────────────────────────

    public static function patients(): string           { return '/admin/patients'; }
    public static function patientCreate(): string      { return '/admin/patients/create'; }
    public static function patient(int $id): string     { return "/admin/patients/{$id}"; }
    public static function patientEdit(int $id): string { return "/admin/patients/{$id}/edit"; }

    // ─── Services ─────────────────────────────────────────────────────────────

    public static function services(): string           { return '/admin/services'; }
    public static function serviceCreate(): string      { return '/admin/services/create'; }
    public static function service(int $id): string     { return "/admin/services/{$id}"; }
    public static function serviceEdit(int $id): string { return "/admin/services/{$id}/edit"; }

    // ─── Events ───────────────────────────────────────────────────────────────

    public static function events(): string             { return '/admin/events'; }
    public static function eventCreate(): string        { return '/admin/events/create'; }
    public static function event(int $id): string       { return "/admin/events/{$id}"; }
    public static function eventEdit(int $id): string   { return "/admin/events/{$id}/edit"; }

    // ─── Trainings ────────────────────────────────────────────────────────────

    public static function trainings(): string              { return '/admin/trainings'; }
    public static function trainingCreate(): string         { return '/admin/trainings/create'; }
    public static function training(int $id): string        { return "/admin/trainings/{$id}"; }
    public static function trainingEdit(int $id): string    { return "/admin/trainings/{$id}/edit"; }

    // ─── Testimonials ─────────────────────────────────────────────────────────

    public static function testimonials(): string           { return '/admin/testimonials'; }
    public static function testimonialEdit(int $id): string { return "/admin/testimonials/{$id}/edit"; }

    // ─── Urgent Bookings ──────────────────────────────────────────────────────

    public static function urgentBookings(): string           { return '/admin/urgent-bookings'; }
    public static function urgentBooking(int $id): string     { return "/admin/urgent-bookings/{$id}"; }
    public static function urgentBookingEdit(int $id): string { return "/admin/urgent-bookings/{$id}/edit"; }

    // ─── Dashboard ────────────────────────────────────────────────────────────

    public static function dashboard(): string          { return '/admin'; }
}
