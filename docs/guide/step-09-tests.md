# Step 9: Comprehensive Test Suite

All new features must have tests. Additionally, existing features lacking tests need coverage. Use **Pest** syntax for all new tests. Use **Pest Browser Testing** (`pestphp/pest-plugin-browser`) for Filament admin panel tests.

---

## 9.1 — Install Dependencies

```bash
composer require pestphp/pest --dev              # if not already installed
composer require pestphp/pest-plugin-laravel --dev
composer require pestphp/pest-plugin-browser --dev
```

Configure Pest browser testing per: https://pestphp.com/docs/browser-testing

---

## 9.2 — Test Organization

```
tests/
├── Feature/
│   ├── Api/
│   │   ├── Auth/
│   │   │   ├── RegisterTest.php
│   │   │   ├── LoginTest.php
│   │   │   ├── LogoutTest.php
│   │   │   ├── RefreshTokenTest.php
│   │   │   └── EmailVerificationTest.php
│   │   ├── Profile/
│   │   │   ├── ShowProfileTest.php
│   │   │   ├── UpdateProfileTest.php
│   │   │   └── UpdatePasswordTest.php
│   │   ├── Appointments/
│   │   │   ├── ListPatientAppointmentsTest.php
│   │   │   ├── ShowPatientAppointmentTest.php
│   │   │   ├── RequestCancellationTest.php
│   │   │   └── RequestRescheduleTest.php
│   │   ├── Booking/
│   │   │   ├── CheckAvailabilityTest.php
│   │   │   ├── BookAppointmentTest.php
│   │   │   └── DoctorAvailabilityTest.php
│   │   ├── Events/
│   │   │   ├── ListEventsTest.php
│   │   │   └── ShowEventTest.php
│   │   ├── Trainings/
│   │   │   ├── ListTrainingsTest.php
│   │   │   ├── ShowTrainingTest.php
│   │   │   └── SubmitReviewTest.php
│   │   ├── Testimonials/
│   │   │   ├── ListTestimonialsTest.php
│   │   │   └── ShowTestimonialTest.php
│   │   ├── Notifications/
│   │   │   ├── ListNotificationsTest.php
│   │   │   ├── MarkAsReadTest.php
│   │   │   └── UnreadCountTest.php
│   │   └── UrgentBooking/
│   │       ├── SubmitUrgentBookingTest.php
│   │       └── ListPatientUrgentBookingsTest.php
│   ├── Conventions/
│   │   ├── TranslatableResponseTest.php
│   │   └── MediaResponseTest.php
│   ├── Booking/                     # existing tests (keep)
│   │   ├── ComprehensiveBookingTest.php
│   │   ├── AppointmentUpdateAvailabilityTest.php
│   │   ├── AppointmentZapSyncTest.php
│   │   ├── BookingApiTest.php
│   │   └── DoctorAvailabilityTest.php
│   └── Notifications/
│       └── NotificationDispatchTest.php
├── Unit/
│   ├── Actions/
│   │   ├── Booking/
│   │   │   ├── CheckAvailabilitySlotTest.php
│   │   │   ├── CreateAppointmentTest.php
│   │   │   └── GetDoctorAvailabilityTest.php
│   │   ├── Patient/
│   │   │   ├── RequestCancellationTest.php
│   │   │   └── RequestRescheduleTest.php
│   │   └── UrgentBooking/
│   │       └── SubmitUrgentBookingTest.php
│   ├── Models/
│   │   ├── AppointmentTest.php
│   │   ├── PatientNotificationTest.php
│   │   ├── UrgentBookingTest.php
│   │   └── ReviewTest.php
│   └── Services/
│       └── PatientNotificationServiceTest.php
├── Browser/
│   ├── Filament/
│   │   ├── AppointmentManagementTest.php
│   │   ├── UrgentBookingManagementTest.php
│   │   ├── DoctorManagementTest.php
│   │   ├── PatientManagementTest.php
│   │   ├── ServiceManagementTest.php
│   │   ├── EventManagementTest.php
│   │   ├── TrainingManagementTest.php
│   │   ├── TestimonialManagementTest.php
│   │   ├── DashboardTest.php
│   │   └── DashboardWidgetsTest.php
│   └── README.md
└── Support/
    ├── DoctorSchedulingHelpers.php   # existing
    └── RelaxedValidationService.php  # existing
```

---

## 9.3 — Feature Test Examples

### Auth Tests

```php
// tests/Feature/Api/Auth/RegisterTest.php

use App\Models\Patient;

it('registers a new patient successfully', function () {
    $response = $this->postJson('/api/v1/patient/auth/register', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'phone' => '201234567890',
        'age' => 30,
        'gender' => 'male',
        'password' => 'secure123',
        'password_confirmation' => 'secure123',
    ]);

    $response->assertOk()
        ->assertJsonStructure(['success', 'data' => ['token', 'refresh_token', 'patient']]);

    expect(Patient::where('email', 'john@example.com')->exists())->toBeTrue();
});

it('sends verification email on registration', function () {
    Mail::fake();

    $this->postJson('/api/v1/patient/auth/register', [
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'jane@example.com',
        'phone' => '201234567891',
        'age' => 25,
        'gender' => 'female',
        'password' => 'secure123',
        'password_confirmation' => 'secure123',
    ])->assertOk();

    Mail::assertSent(VerifyEmailMail::class, fn($mail) => $mail->hasTo('jane@example.com'));
});

it('rejects duplicate email', function () {
    Patient::factory()->create(['email' => 'taken@example.com']);

    $this->postJson('/api/v1/patient/auth/register', [
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'taken@example.com',
        'phone' => '201234567892',
        'age' => 20,
        'gender' => 'male',
        'password' => 'secure123',
        'password_confirmation' => 'secure123',
    ])->assertStatus(422);
});
```

### Email Verification Tests

```php
// tests/Feature/Api/Auth/EmailVerificationTest.php

it('verifies email with valid token', function () {
    $patient = Patient::factory()->create([
        'email_verification_token' => 'valid-token',
        'email_verification_sent_at' => now(),
        'email_verified_at' => null,
    ]);

    $this->postJson('/api/v1/patient/auth/verify-email', [
        'email' => $patient->email,
        'token' => 'valid-token',
    ])->assertOk()
      ->assertJsonPath('success', true);

    expect($patient->fresh()->hasVerifiedEmail())->toBeTrue();
});

it('rejects expired token', function () {
    $patient = Patient::factory()->create([
        'email_verification_token' => 'expired-token',
        'email_verification_sent_at' => now()->subHours(25),
        'email_verified_at' => null,
    ]);

    $this->postJson('/api/v1/patient/auth/verify-email', [
        'email' => $patient->email,
        'token' => 'expired-token',
    ])->assertStatus(422);
});

it('blocks unverified patient from booking', function () {
    $patient = Patient::factory()->create(['email_verified_at' => null]);
    // ... setup doctor, service, availability ...

    $this->actingAs($patient, 'sanctum')
        ->postJson("/api/v1/booking/{$doctor->id}/{$service->id}/book", [...])
        ->assertStatus(403);
});
```

### Patient Appointments Tests

```php
// tests/Feature/Api/Appointments/ListPatientAppointmentsTest.php

it('lists only authenticated patient appointments', function () {
    $patient1 = Patient::factory()->create();
    $patient2 = Patient::factory()->create();
    Appointment::factory()->count(3)->create(['patient_id' => $patient1->id]);
    Appointment::factory()->count(2)->create(['patient_id' => $patient2->id]);

    $this->actingAs($patient1, 'sanctum')
        ->getJson('/api/v1/patient/appointments')
        ->assertOk()
        ->assertJsonCount(3, 'data.data');
});

it('filters appointments by status', function () {
    $patient = Patient::factory()->create();
    Appointment::factory()->create(['patient_id' => $patient->id, 'status' => 'pending']);
    Appointment::factory()->create(['patient_id' => $patient->id, 'status' => 'confirmed']);

    $this->actingAs($patient, 'sanctum')
        ->getJson('/api/v1/patient/appointments?status=pending')
        ->assertOk()
        ->assertJsonCount(1, 'data.data');
});
```

### Cancellation & Reschedule Tests

```php
// tests/Feature/Api/Appointments/RequestCancellationTest.php

it('submits cancellation request', function () {
    $patient = Patient::factory()->create();
    $appointment = Appointment::factory()->create([
        'patient_id' => $patient->id,
        'status' => 'confirmed',
    ]);

    $this->actingAs($patient, 'sanctum')
        ->postJson("/api/v1/patient/appointments/{$appointment->id}/cancel", [
            'reason' => 'Cannot make it due to travel plans',
        ])
        ->assertOk()
        ->assertJsonPath('data.change_request_status', 'pending_cancellation');
});

it('prevents cancelling already cancelled appointment', function () {
    $patient = Patient::factory()->create();
    $appointment = Appointment::factory()->create([
        'patient_id' => $patient->id,
        'status' => 'cancelled',
    ]);

    $this->actingAs($patient, 'sanctum')
        ->postJson("/api/v1/patient/appointments/{$appointment->id}/cancel", [
            'reason' => 'Some reason',
        ])
        ->assertStatus(422);
});

it('prevents patient from cancelling another patients appointment', function () {
    $patient1 = Patient::factory()->create();
    $patient2 = Patient::factory()->create();
    $appointment = Appointment::factory()->create(['patient_id' => $patient1->id]);

    $this->actingAs($patient2, 'sanctum')
        ->postJson("/api/v1/patient/appointments/{$appointment->id}/cancel", [
            'reason' => 'Some reason',
        ])
        ->assertStatus(403);
});
```

### Notification Tests

```php
// tests/Feature/Api/Notifications/ListNotificationsTest.php

it('lists patient notifications in current locale', function () {
    $patient = Patient::factory()->create();
    PatientNotification::create([
        'patient_id' => $patient->id,
        'type' => 'appointment_confirmed',
        'title' => ['en' => 'Confirmed', 'ar' => 'تم التأكيد', 'fr' => 'Confirmé'],
        'body' => ['en' => 'Your appointment is confirmed', 'ar' => 'تم تأكيد موعدك', 'fr' => 'Votre rdv est confirmé'],
    ]);

    $this->actingAs($patient, 'sanctum')
        ->getJson('/api/v1/patient/notifications')
        ->assertOk()
        ->assertJsonPath('data.data.0.title', 'Confirmed');
});

it('returns correct unread count', function () {
    $patient = Patient::factory()->create();
    PatientNotification::factory()->count(3)->create(['patient_id' => $patient->id, 'read_at' => null]);
    PatientNotification::factory()->count(2)->create(['patient_id' => $patient->id, 'read_at' => now()]);

    $this->actingAs($patient, 'sanctum')
        ->getJson('/api/v1/patient/notifications/unread-count')
        ->assertOk()
        ->assertJsonPath('data.unread_count', 3);
});
```

### Urgent Booking Tests

```php
// tests/Feature/Api/UrgentBooking/SubmitUrgentBookingTest.php

it('allows visitor to submit urgent booking without auth', function () {
    $this->postJson('/api/v1/urgent-booking/submit', [
        'patient_name' => 'John Doe',
        'patient_phone' => '+213555123456',
        'reason' => 'Severe toothache that started suddenly',
    ])
    ->assertStatus(201)
    ->assertJsonPath('data.status', 'pending');
});

it('sends admin notification on urgent booking', function () {
    Notification::fake();

    $this->postJson('/api/v1/urgent-booking/submit', [
        'patient_name' => 'Jane Doe',
        'patient_phone' => '+213555654321',
        'reason' => 'Emergency dental pain, needs immediate help',
    ])->assertStatus(201);

    Notification::assertSentTo(User::first(), NewUrgentBookingReceived::class);
});

it('auto-fills patient_id for authenticated patients', function () {
    $patient = Patient::factory()->create();

    $this->actingAs($patient, 'sanctum')
        ->postJson('/api/v1/urgent-booking/submit', [
            'patient_name' => $patient->full_name,
            'patient_phone' => $patient->phone,
            'reason' => 'Urgent dental emergency requiring immediate attention',
        ])
        ->assertStatus(201);

    expect(UrgentBooking::first()->patient_id)->toBe($patient->id);
});
```

### Notification Dispatch Tests

```php
// tests/Feature/Notifications/NotificationDispatchTest.php

it('sends admin notification when appointment is booked', function () {
    Notification::fake();

    // ... create doctor, service, availability, patient ...
    // ... book appointment via API ...

    Notification::assertSentTo(User::first(), NewAppointmentBooked::class);
});

it('sends patient notification when admin confirms appointment', function () {
    // ... create appointment with pending status ...
    // ... (simulate admin action or call action directly) ...

    expect(PatientNotification::where('patient_id', $patient->id)
        ->where('type', 'appointment_confirmed')
        ->exists()
    )->toBeTrue();
});
```

---

## 9.4 — Browser Tests (Filament Admin)

Using `pestphp/pest-plugin-browser` per https://pestphp.com/docs/browser-testing

### Setup

```php
// tests/Browser/Filament/AppointmentManagementTest.php
use function Pest\Browser\visit;
```

### Appointment Management Browser Tests

```php
it('can view appointments list in Filament', function () {
    $admin = User::factory()->create();
    Appointment::factory()->count(5)->create();

    visit(route('filament.admin.resources.appointments.index'))
        ->loginAs($admin)
        ->assertSee('Appointments')
        ->assertSeeCount('.fi-ta-row', 5);
});

it('can confirm a pending appointment', function () {
    $admin = User::factory()->create();
    $appointment = Appointment::factory()->create(['status' => 'pending']);

    visit(route('filament.admin.resources.appointments.index'))
        ->loginAs($admin)
        ->click('[data-action="confirm"]')  // adjust selector based on Filament's actual markup
        ->waitForDialog()
        ->click('Confirm')
        ->waitForReload()
        ->assertSee('Confirmed');

    expect($appointment->fresh()->status)->toBe(AppointmentStatus::CONFIRMED);
});

it('can reject a pending appointment with reason', function () {
    $admin = User::factory()->create();
    $appointment = Appointment::factory()->create(['status' => 'pending']);

    visit(route('filament.admin.resources.appointments.index'))
        ->loginAs($admin)
        ->click('[data-action="reject"]')
        ->waitForDialog()
        ->type('admin_notes', 'Doctor not available on this date')
        ->click('Reject')
        ->waitForReload()
        ->assertSee('Rejected');
});

it('can approve cancellation request', function () {
    $admin = User::factory()->create();
    $appointment = Appointment::factory()->create([
        'status' => 'confirmed',
        'change_request_status' => 'pending_cancellation',
    ]);

    visit(route('filament.admin.resources.appointments.index'))
        ->loginAs($admin)
        ->assertSee('Pending Cancellation')
        ->click('[data-action="approve_cancellation"]')
        ->waitForDialog()
        ->click('Confirm')
        ->waitForReload();

    expect($appointment->fresh()->status)->toBe(AppointmentStatus::CANCELLED);
});

it('can approve reschedule request', function () {
    $admin = User::factory()->create();
    $appointment = Appointment::factory()->create([
        'status' => 'confirmed',
        'change_request_status' => 'pending_reschedule',
        'requested_new_from' => now()->addDays(3)->setHour(10),
        'requested_new_to' => now()->addDays(3)->setHour(10)->addMinutes(30),
    ]);

    visit(route('filament.admin.resources.appointments.index'))
        ->loginAs($admin)
        ->click('[data-action="approve_reschedule"]')
        ->waitForDialog()
        ->click('Confirm')
        ->waitForReload();

    expect($appointment->fresh()->change_request_status)->toBe('approved');
});
```

### Urgent Booking Browser Tests

```php
it('can view and accept urgent booking in Filament', function () {
    $admin = User::factory()->create();
    $doctor = Doctor::factory()->create();
    $booking = UrgentBooking::factory()->create(['status' => 'pending']);

    visit(route('filament.admin.resources.urgent-bookings.index'))
        ->loginAs($admin)
        ->assertSee($booking->patient_name)
        ->click('[data-action="accept"]')
        ->waitForDialog()
        ->select('assigned_doctor_id', $doctor->id)
        ->type('scheduled_datetime', now()->addHours(2)->format('Y-m-d H:i'))
        ->click('Accept')
        ->waitForReload()
        ->assertSee('Accepted');
});
```

### Dashboard Widgets Browser Tests

```php
it('displays pending counts on dashboard', function () {
    $admin = User::factory()->create();
    Appointment::factory()->count(3)->create(['status' => 'pending']);
    UrgentBooking::factory()->count(2)->create(['status' => 'pending']);

    visit(route('filament.admin.pages.dashboard'))
        ->loginAs($admin)
        ->assertSee('3')      // pending appointments
        ->assertSee('2');     // urgent bookings
});
```

---

## 9.5 — Filament Resource Browser Tests (ALL Resources)

Test every Filament resource to ensure CRUD operations work correctly.

### Event Management Browser Tests

```php
// tests/Browser/Filament/EventManagementTest.php

it('can list events in Filament', function () {
    $admin = User::factory()->create();
    Event::factory()->count(3)->create();

    visit(route('filament.admin.resources.events.index'))
        ->loginAs($admin)
        ->assertSee('Events');
});

it('can create an event with all new fields', function () {
    $admin = User::factory()->create();

    visit(route('filament.admin.resources.events.create'))
        ->loginAs($admin)
        ->type('title.en', 'Dental Health Day')
        ->type('title.ar', 'يوم صحة الأسنان')
        ->type('title.fr', 'Journée de santé dentaire')
        ->type('description.en', 'Annual dental health awareness event')
        ->fillDate('date', now()->addMonth()->format('Y-m-d'))
        ->type('time', '09:00')
        ->type('speakers.en', 'Dr. Smith, Dr. Jones')
        ->type('about_event.en', 'Learn about dental hygiene')
        ->type('what_to_expect.en', 'Free checkups and consultations')
        ->click('Create')
        ->waitForReload()
        ->assertSee('Dental Health Day');
});

it('can edit an event', function () {
    $admin = User::factory()->create();
    $event = Event::factory()->create();

    visit(route('filament.admin.resources.events.edit', $event))
        ->loginAs($admin)
        ->clear('title.en')
        ->type('title.en', 'Updated Event Title')
        ->click('Save')
        ->waitForReload();

    expect($event->fresh()->getTranslation('title', 'en'))->toBe('Updated Event Title');
});

it('can view event details', function () {
    $admin = User::factory()->create();
    $event = Event::factory()->create();

    visit(route('filament.admin.resources.events.view', $event))
        ->loginAs($admin)
        ->assertSee($event->title);
});

it('can delete an event', function () {
    $admin = User::factory()->create();
    $event = Event::factory()->create();

    visit(route('filament.admin.resources.events.index'))
        ->loginAs($admin)
        ->click('[data-action="delete"]')
        ->waitForDialog()
        ->click('Delete')
        ->waitForReload();

    expect(Event::withTrashed()->find($event->id)->trashed())->toBeTrue();
});
```

### Training Management Browser Tests

```php
// tests/Browser/Filament/TrainingManagementTest.php

it('can create a training with price', function () {
    $admin = User::factory()->create();

    visit(route('filament.admin.resources.trainings.create'))
        ->loginAs($admin)
        ->type('title.en', 'Advanced Dental Techniques')
        ->type('description.en', 'Professional development course')
        ->type('trainer_name', 'Dr. Expert')
        ->type('duration', '120')
        ->type('price', '5000')
        ->click('Create')
        ->waitForReload();

    expect(Training::latest()->first()->price)->toBe(5000);
});

it('can manage training reviews via relation manager', function () {
    $admin = User::factory()->create();
    $training = Training::factory()->create();
    $review = Review::factory()->create([
        'reviewable_type' => Training::class,
        'reviewable_id' => $training->id,
        'is_approved' => false,
    ]);

    visit(route('filament.admin.resources.trainings.edit', $training))
        ->loginAs($admin)
        ->assertSee('Reviews')
        ->assertSee($review->reviewer_name);
});
```

### Doctor Management Browser Tests

```php
// tests/Browser/Filament/DoctorManagementTest.php

it('can list doctors', function () {
    $admin = User::factory()->create();
    Doctor::factory()->count(3)->create();

    visit(route('filament.admin.resources.doctors.index'))
        ->loginAs($admin)
        ->assertSee('Doctors');
});

it('can create a doctor with translatable fields', function () {
    $admin = User::factory()->create();

    visit(route('filament.admin.resources.doctors.create'))
        ->loginAs($admin)
        ->type('name.en', 'Dr. John Smith')
        ->type('name.ar', 'د. جون سميث')
        ->type('name.fr', 'Dr. Jean Smith')
        ->type('specialty.en', 'Orthodontics')
        ->type('email', 'john@clinic.com')
        ->type('phone', '1234567890')
        ->click('Create')
        ->waitForReload();

    expect(Doctor::latest()->first())->not->toBeNull();
});

it('can manage doctor services (many-to-many)', function () {
    $admin = User::factory()->create();
    $doctor = Doctor::factory()->create();
    $service = Service::factory()->create();

    visit(route('filament.admin.resources.doctors.edit', $doctor))
        ->loginAs($admin)
        ->assertSee('Services');
    // Verify service attachment UI is present
});
```

### Patient Management Browser Tests

```php
// tests/Browser/Filament/PatientManagementTest.php

it('can list patients', function () {
    $admin = User::factory()->create();
    Patient::factory()->count(5)->create();

    visit(route('filament.admin.resources.patients.index'))
        ->loginAs($admin)
        ->assertSee('Patients');
});

it('can view patient details with email verification status', function () {
    $admin = User::factory()->create();
    $verifiedPatient = Patient::factory()->create(['email_verified_at' => now()]);
    $unverifiedPatient = Patient::factory()->create(['email_verified_at' => null]);

    visit(route('filament.admin.resources.patients.view', $verifiedPatient))
        ->loginAs($admin)
        ->assertSee('Verified');

    visit(route('filament.admin.resources.patients.view', $unverifiedPatient))
        ->loginAs($admin)
        ->assertSee('Not Verified');
});
```

### Service Management Browser Tests

```php
// tests/Browser/Filament/ServiceManagementTest.php

it('can create a service with translatable name', function () {
    $admin = User::factory()->create();

    visit(route('filament.admin.resources.services.create'))
        ->loginAs($admin)
        ->type('name.en', 'Teeth Whitening')
        ->type('name.ar', 'تبييض الأسنان')
        ->type('name.fr', 'Blanchiment des dents')
        ->type('price', '3000')
        ->type('duration', '60')
        ->click('Create')
        ->waitForReload();

    expect(Service::latest()->first())->not->toBeNull();
});
```

### Testimonial Management Browser Tests

```php
// tests/Browser/Filament/TestimonialManagementTest.php

it('can toggle testimonial publish status', function () {
    $admin = User::factory()->create();
    $testimonial = Testimonial::factory()->create(['is_published' => false]);

    visit(route('filament.admin.resources.testimonials.index'))
        ->loginAs($admin)
        ->assertSee($testimonial->patient_name);
    // Verify publish toggle action is available
});
```

---

## 9.6 — API Response Convention Tests

Test that API responses follow the translation and media conventions from step-10.

```php
// tests/Feature/Api/Conventions/TranslatableResponseTest.php

it('returns all locales for translatable fields on doctors', function () {
    $doctor = Doctor::factory()->create([
        'name' => ['en' => 'Dr. Smith', 'ar' => 'د. سميث', 'fr' => 'Dr. Forgeron'],
    ]);

    $this->getJson('/api/v1/doctors')
        ->assertOk()
        ->assertJsonStructure(['data' => ['data' => [['name' => ['en', 'ar', 'fr']]]]]);
});

it('returns all locales for translatable fields on services', function () {
    $service = Service::factory()->create([
        'name' => ['en' => 'Cleaning', 'ar' => 'تنظيف', 'fr' => 'Nettoyage'],
    ]);

    $this->getJson('/api/v1/services')
        ->assertOk()
        ->assertJsonStructure(['data' => ['data' => [['name' => ['en', 'ar', 'fr']]]]]);
});

it('returns all locales for translatable fields on events', function () {
    $event = Event::factory()->create();

    $this->getJson('/api/v1/events')
        ->assertOk()
        ->assertJsonStructure(['data' => ['data' => [['name' => ['en', 'ar', 'fr'], 'location' => ['en', 'ar', 'fr']]]]]);
});

it('returns error messages in requested locale', function () {
    $this->withHeader('Accept-Language', 'ar')
        ->postJson('/api/v1/patient/auth/login', [])
        ->assertStatus(422);
    // Verify validation messages are in Arabic
});
```

```php
// tests/Feature/Api/Conventions/MediaResponseTest.php

it('returns doctor image with original and thumb conversions', function () {
    $doctor = Doctor::factory()->create();
    // Attach a media file to doctor_photo collection
    $doctor->addMedia(UploadedFile::fake()->image('photo.jpg'))->toMediaCollection('doctor_photo');

    $this->getJson('/api/v1/doctors')
        ->assertOk()
        ->assertJsonStructure(['data' => ['data' => [['image' => ['original', 'thumb']]]]]);
});

it('returns null image when no media attached', function () {
    $doctor = Doctor::factory()->create();

    $this->getJson('/api/v1/doctors')
        ->assertOk()
        ->assertJsonPath('data.data.0.image', null);
});

it('returns event gallery with original and thumb per image', function () {
    $event = Event::factory()->create();
    $event->addMedia(UploadedFile::fake()->image('pic1.jpg'))->toMediaCollection('gallery');
    $event->addMedia(UploadedFile::fake()->image('pic2.jpg'))->toMediaCollection('gallery');

    $this->getJson("/api/v1/events/{$event->id}")
        ->assertOk()
        ->assertJsonStructure(['data' => ['gallery' => [['id', 'original', 'thumb']]]]);
});
```

---

## 9.7 — Dashboard Widget Tests

```php
// tests/Browser/Filament/DashboardTest.php

it('displays all dashboard widgets', function () {
    $admin = User::factory()->create();

    visit(route('filament.admin.pages.dashboard'))
        ->loginAs($admin)
        ->assertSee('Total Patients')
        ->assertSee("Today's Appointments")
        ->assertSee('Pending Actions');
});

it('shows correct stats in overview widget', function () {
    $admin = User::factory()->create();
    Patient::factory()->count(10)->create();
    Appointment::factory()->count(3)->create(['from' => today()->setHour(10), 'status' => 'confirmed']);
    Appointment::factory()->count(2)->create(['status' => 'pending']);

    visit(route('filament.admin.pages.dashboard'))
        ->loginAs($admin)
        ->assertSee('10')   // patients
        ->assertSee('3')    // today appointments
        ->assertSee('2');   // pending
});

it('today appointments table shows correct appointments', function () {
    $admin = User::factory()->create();
    $todayAppt = Appointment::factory()->create(['from' => today()->setHour(14)]);
    $tomorrowAppt = Appointment::factory()->create(['from' => tomorrow()->setHour(10)]);

    visit(route('filament.admin.pages.dashboard'))
        ->loginAs($admin)
        ->assertSee($todayAppt->patient->full_name)
        ->assertDontSee($tomorrowAppt->patient->full_name);
});
```

---

## 9.8 — Factories Needed

Create or update factories for test data:

| Factory | File |
|---------|------|
| `PatientFactory` | `database/factories/PatientFactory.php` — ensure it exists and is complete |
| `AppointmentFactory` | `database/factories/AppointmentFactory.php` — needs updating for new fields |
| `UrgentBookingFactory` | `database/factories/UrgentBookingFactory.php` — **new** |
| `PatientNotificationFactory` | `database/factories/PatientNotificationFactory.php` — **new** |
| `ReviewFactory` | `database/factories/ReviewFactory.php` — **new** |
| `EventFactory` | `database/factories/EventFactory.php` — update for new fields |
| `TrainingFactory` | `database/factories/TrainingFactory.php` — update for price |
| `UserFactory` | `database/factories/UserFactory.php` — ensure admin user factory exists |
| `DoctorFactory` | `database/factories/DoctorFactory.php` — ensure translatable fields are set |
| `ServiceFactory` | `database/factories/ServiceFactory.php` — ensure translatable name |
| `TestimonialFactory` | `database/factories/TestimonialFactory.php` — ensure it exists |

---

## 9.9 — Test Helpers

### Existing Helpers (Keep)
- `tests/Support/DoctorSchedulingHelpers.php` — Zap scheduling helpers for booking tests
- `tests/Support/RelaxedValidationService.php` — relaxed Zap validation for tests

### New Helpers to Consider
- `tests/Support/PatientTestHelpers.php` — helper to create authenticated patient with token
- `tests/Support/FilamentTestHelpers.php` — helper to create admin user and login for browser tests

---

## 9.10 — Running Tests

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/Api/Auth/RegisterTest.php

# Run browser tests
php artisan test tests/Browser/

# Run with coverage
php artisan test --coverage

# Run only Pest tests
./vendor/bin/pest
```
