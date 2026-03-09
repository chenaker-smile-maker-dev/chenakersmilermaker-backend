<?php

namespace Tests\Browser\Core;

/**
 * DemoData — references to the fixed data seeded by DemoSeeder.
 *
 * Use these constants/methods in browser tests so fixture data is declared in
 * one place. When DemoSeeder changes, only this file needs updating.
 */
class DemoData
{
    // ─── Admin ────────────────────────────────────────────────────────────────

    public const ADMIN_EMAIL    = 'admin@clinic.dz';
    public const ADMIN_PASSWORD = 'password';

    // ─── Demo patient ─────────────────────────────────────────────────────────

    public const PATIENT_EMAIL    = 'patient@demo.dz';
    public const PATIENT_PASSWORD = 'password';
    public const PATIENT_NAME     = 'Mohamed Amrani';

    // ─── Doctors ─────────────────────────────────────────────────────────────

    public const DOCTOR_NAMES = [
        'Dr. Amina Belhocine',
        'Dr. Karim Ouahrani',
        'Dr. Fatima Ziani',
        'Dr. Younes Meziane',
        'Dr. Sara Bencherif',
    ];

    // ─── Services ─────────────────────────────────────────────────────────────

    public const SERVICE_NAMES = [
        'Dental Cleaning',
        'Teeth Whitening',
        'Dental Implant',
        'Root Canal Treatment',
        'Tooth Extraction',
        'Dental Braces',
        'Ceramic Crown',
        'Emergency Consultation',
        'Pediatric Dentistry',
        'Dental X-Ray',
    ];

    // ─── Appointment statuses ─────────────────────────────────────────────────

    public const APPOINTMENT_STATUS_CONFIRMED = 'Confirmed';
    public const APPOINTMENT_STATUS_PENDING   = 'Pending';
    public const APPOINTMENT_STATUS_COMPLETED = 'Completed';
    public const APPOINTMENT_STATUS_CANCELLED = 'Cancelled';
    public const APPOINTMENT_STATUS_REJECTED  = 'Rejected';

    // ─── Events ───────────────────────────────────────────────────────────────

    public const EVENT_TITLES = [
        'Oral Health Awareness Day',
        'Modern Implantology Workshop',
        "Children's Smile Day",
        'Annual Dental Conference 2025',
    ];

    // ─── Trainings ────────────────────────────────────────────────────────────

    public const TRAINING_TITLES = [
        'Advanced Root Canal Techniques',
        'Orthodontic Essentials for General Dentists',
        'Pediatric Dentistry: Behavior Management',
        'Digital Dentistry & CAD/CAM Systems',
    ];

    // ─── Urgent Booking ───────────────────────────────────────────────────────

    public const URGENT_REASON = 'Severe toothache since last night, cannot sleep.';
}
