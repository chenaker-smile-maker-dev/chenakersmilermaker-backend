# Implementation Todolist

Track progress by marking items with `[x]` when completed.

---

## Step 1: Project Overview & Gap Analysis
- [ ] Read and understand all existing models, controllers, routes, actions
- [ ] Read docs/new/events.txt and docs/new/training(learning).txt specs
- [ ] Identify all files that need modification vs creation

## Step 2: Database Migrations & Model Changes
- [ ] Migration: Add columns to appointments table (admin_notes, cancellation_reason, reschedule_reason, change_request_status, requested_new_from, requested_new_to, original_from, original_to, confirmed_by, confirmed_at)
- [ ] Migration: Add columns to patients table (email_verification_token, email_verification_sent_at)
- [ ] Migration: Add columns to events table (time, speakers, about_event, what_to_expect)
- [ ] Migration: Add price column to trainings table
- [ ] Migration: Create patient_notifications table
- [ ] Migration: Create urgent_bookings table
- [ ] Migration: Create reviews table
- [ ] Model: Create PatientNotification model
- [ ] Model: Create UrgentBooking model
- [ ] Model: Create Review model
- [ ] Model: Update Appointment model with new fields, casts, relations
- [ ] Model: Update Patient model with verification fields, notifications relation
- [ ] Model: Fix Event model (implement HasMedia, InteractsWithMedia, add new fields)
- [ ] Model: Update Training model (price, images collection, reviews relation)
- [ ] Enum: Create UrgentBookingStatus enum
- [ ] Enum: Create PatientNotificationType enum
- [ ] Enum: Create ChangeRequestStatus enum
- [ ] Run migrations and verify

## Step 3: Patient Appointments API
- [ ] Create PatientAppointmentController
- [ ] Create ListPatientAppointments action
- [ ] Create ShowPatientAppointment action
- [ ] Create RequestAppointmentCancellation action
- [ ] Create RequestAppointmentReschedule action
- [ ] Add routes to routes/api/v1.php
- [ ] Add Scramble attributes to controller
- [ ] Test endpoints manually

## Step 4: Notification System
- [ ] Create PatientNotificationService
- [ ] Create PatientNotificationTemplates (translatable ar/fr/en)
- [ ] Create PatientNotificationController (list, unread-count, mark-read, mark-all-read, delete)
- [ ] Add notification routes
- [ ] Create Filament notification classes (NewAppointmentBooked, CancellationRequested, RescheduleRequested, NewUrgentBookingReceived, NewReviewSubmitted)
- [ ] Enable databaseNotifications() in AdminPanelProvider (already enabled — verify)
- [ ] Wire up notification dispatch in all relevant actions (booking, cancellation, reschedule, urgent booking, review)
- [ ] Test notification flow

## Step 5: Admin Appointment Management (Filament)
- [ ] Upgrade AppointmentResource table (columns, filters, actions)
- [ ] Add Confirm action
- [ ] Add Reject action (with reason form)
- [ ] Add Complete action
- [ ] Add Approve Cancellation action
- [ ] Add Reject Cancellation action
- [ ] Add Approve Reschedule action
- [ ] Add Reject Reschedule action
- [ ] Add bulk actions (confirm selected, reject selected)
- [ ] Upgrade ViewAppointment page (patient info, change request section, admin notes)
- [ ] Create PendingAppointmentsWidget (moved to step 10 dashboard)
- [ ] All admin actions dispatch patient notifications

## Step 6: Email Verification
- [ ] Create base email Blade layout (RTL support for Arabic)
- [ ] Create verify-email Blade template
- [ ] Create VerifyEmailMail mailable
- [ ] Create SendVerificationEmail action
- [ ] Create VerifyEmail action
- [ ] Add verify-email and resend-verification API endpoints
- [ ] Add EnsureEmailIsVerified middleware
- [ ] Gate booking behind email verification
- [ ] Send verification email on registration (update RegisterPatient action)
- [ ] Test full verification flow

## Step 7: Urgent Booking System
- [ ] Create UrgentBookingController (submit, list, show)
- [ ] Create SubmitUrgentBooking action
- [ ] Add routes (submit is public, list/show require auth)
- [ ] Create UrgentBookingResource in Filament
- [ ] Add Accept action (assign doctor, schedule time)
- [ ] Add Reject action
- [ ] Add Complete action
- [ ] Create UrgentBookingsWidget for dashboard
- [ ] Send admin notification on new urgent booking
- [ ] Test urgent booking flow

## Step 8: Event & Training Updates
- [ ] Fix Event model: implement HasMedia, add InteractsWithMedia
- [ ] Add new Event fields to translatable array
- [ ] Add status accessor and scopes (archive, happening, future)
- [ ] Update ListEvents action with type filter and new response fields
- [ ] Update ShowEvent action with new response fields
- [ ] Update EventResource in Filament (new form fields, fix media upload)
- [ ] Add Training price field
- [ ] Add Training images collection (multiple)
- [ ] Add Training reviews relation
- [ ] Update ListTrainings action with new response fields
- [ ] Update ShowTraining action with reviews
- [ ] Create SubmitTrainingReview action + endpoint
- [ ] Update TrainingResource in Filament (price, images, reviews relation manager)
- [ ] Create Review approval in Filament (relation manager or standalone resource)

## Step 9: Tests
- [ ] Create/update all factories (Patient, Appointment, UrgentBooking, PatientNotification, Review, Event, Training, User, Doctor, Service, Testimonial)
- [ ] Feature tests: Auth (register, login, logout, refresh, email verification)
- [ ] Feature tests: Profile (show, update, update password)
- [ ] Feature tests: Patient appointments (list, show, cancel, reschedule)
- [ ] Feature tests: Booking (check availability, book appointment)
- [ ] Feature tests: Notifications (list, unread count, mark read, mark all, delete)
- [ ] Feature tests: Urgent booking (submit, list, show)
- [ ] Feature tests: Events (list with type filter, show)
- [ ] Feature tests: Trainings (list, show, submit review)
- [ ] Feature tests: Testimonials (list, show)
- [ ] Feature tests: API conventions (translatable responses, media responses)
- [ ] Unit tests: Actions (booking, cancellation, reschedule, urgent booking)
- [ ] Unit tests: Models (Appointment, PatientNotification, UrgentBooking, Review)
- [ ] Unit tests: PatientNotificationService
- [ ] Browser tests: Appointment management (confirm, reject, complete, approve/reject changes)
- [ ] Browser tests: Urgent booking management (accept, reject, complete)
- [ ] Browser tests: Event CRUD
- [ ] Browser tests: Training CRUD + reviews
- [ ] Browser tests: Doctor CRUD
- [ ] Browser tests: Patient management + verification status
- [ ] Browser tests: Service CRUD
- [ ] Browser tests: Testimonial management
- [ ] Browser tests: Dashboard widgets (stats, today's appointments, pending actions)

## Step 10: Dashboard, API Conventions & Polish
- [ ] Create MediaHelper utility (app/Utils/MediaHelper.php)
- [ ] Create lang/en/api.php, lang/ar/api.php, lang/fr/api.php with all API messages
- [ ] Update all API Resources to use GetModelMultilangAttribute for ALL translatable fields
- [ ] Fix TestimonialResource (currently returns single locale)
- [ ] Update all Actions to use MediaHelper for image responses
- [ ] Update all Actions to use __() for translated messages
- [ ] Create custom Dashboard page (app/Filament/Admin/Pages/Dashboard.php)
- [ ] Create StatsOverviewWidget (total patients, today appointments, monthly stats, pending actions)
- [ ] Create PendingActionsWidget (pending appointments, cancellation requests, reschedule requests)
- [ ] Create TodayAppointmentsWidget (table of today's appointments)
- [ ] Create RecentActivityWidget (activity feed)
- [ ] Create recent-activity Blade view
- [ ] Update AdminPanelProvider (use custom Dashboard, clean up widget registration)
- [ ] Set $sort and $columnSpan on existing AppointmentCalendarWidget
- [ ] Polish all Filament resources (Events: new fields, Trainings: price/images/reviews, Patients: verification status)
- [ ] Final run of all tests
- [ ] Verify all API responses follow conventions

---

## Completion Checklist
- [ ] All migrations run successfully
- [ ] All API endpoints return correct translatable format ({ar, fr, en})
- [ ] All API endpoints return images with {original, thumb} format
- [ ] All error messages are translatable via lang files
- [ ] Dashboard has all widgets and shows correct data
- [ ] All Filament resources CRUD works
- [ ] All tests pass
- [ ] Code committed
