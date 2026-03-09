# Implementation Todolist

Track progress by marking items with `[x]` when completed.

---

## Step 1: Project Overview & Gap Analysis
- [x] Read and understand all existing models, controllers, routes, actions
- [x] Read docs/new/events.txt and docs/new/training(learning).txt specs
- [x] Identify all files that need modification vs creation

## Step 2: Database Migrations & Model Changes
- [x] Migration: Add columns to appointments table (admin_notes, cancellation_reason, reschedule_reason, change_request_status, requested_new_from, requested_new_to, original_from, original_to, confirmed_by, confirmed_at)
- [x] Migration: Add columns to patients table (email_verification_token, email_verification_sent_at)
- [x] Migration: Add columns to events table (time, speakers, about_event, what_to_expect)
- [x] Migration: Add price column to trainings table
- [x] Migration: Create patient_notifications table
- [x] Migration: Create urgent_bookings table
- [x] Migration: Create reviews table
- [x] Model: Create PatientNotification model
- [x] Model: Create UrgentBooking model
- [x] Model: Create Review model
- [x] Model: Update Appointment model with new fields, casts, relations
- [x] Model: Update Patient model with verification fields, notifications relation
- [x] Model: Fix Event model (implement HasMedia, InteractsWithMedia, add new fields)
- [x] Model: Update Training model (price, images collection, reviews relation)
- [x] Enum: Create UrgentBookingStatus enum
- [x] Enum: Create PatientNotificationType enum
- [x] Enum: Create ChangeRequestStatus enum
- [x] Run migrations and verify

## Step 3: Patient Appointments API
- [x] Create PatientAppointmentController
- [x] Create ListPatientAppointments action
- [x] Create ShowPatientAppointment action
- [x] Create RequestAppointmentCancellation action
- [x] Create RequestAppointmentReschedule action
- [x] Add routes to routes/api/v1.php
- [x] Add Scramble attributes to controller
- [x] Test endpoints manually

## Step 4: Notification System
- [x] Create PatientNotificationService
- [x] Create PatientNotificationTemplates (translatable ar/fr/en)
- [x] Create PatientNotificationController (list, unread-count, mark-read, mark-all-read, delete)
- [x] Add notification routes
- [x] Create Filament notification classes (NewAppointmentBooked, CancellationRequested, RescheduleRequested, NewUrgentBookingReceived, NewReviewSubmitted)
- [x] Enable databaseNotifications() in AdminPanelProvider (already enabled — verify)
- [x] Wire up notification dispatch in all relevant actions (booking, cancellation, reschedule, urgent booking, review)
- [x] Test notification flow

## Step 5: Admin Appointment Management (Filament)
- [x] Upgrade AppointmentResource table (columns, filters, actions)
- [x] Add Confirm action
- [x] Add Reject action (with reason form)
- [x] Add Complete action
- [x] Add Approve Cancellation action
- [x] Add Reject Cancellation action
- [x] Add Approve Reschedule action
- [x] Add Reject Reschedule action
- [x] Add bulk actions (confirm selected, reject selected)
- [x] Upgrade ViewAppointment page (patient info, change request section, admin notes)
- [x] Create PendingAppointmentsWidget (moved to step 10 dashboard)
- [x] All admin actions dispatch patient notifications

## Step 6: Email Verification
- [x] Create base email Blade layout (RTL support for Arabic)
- [x] Create verify-email Blade template
- [x] Create VerifyEmailMail mailable
- [x] Create SendVerificationEmail action
- [x] Create VerifyEmail action
- [x] Add verify-email and resend-verification API endpoints
- [x] Add EnsureEmailIsVerified middleware
- [x] Gate booking behind email verification
- [x] Send verification email on registration (update RegisterPatient action)
- [x] Test full verification flow

## Step 7: Urgent Booking System
- [x] Create UrgentBookingController (submit, list, show)
- [x] Create SubmitUrgentBooking action
- [x] Add routes (submit is public, list/show require auth)
- [x] Create UrgentBookingResource in Filament
- [x] Add Accept action (assign doctor, schedule time)
- [x] Add Reject action
- [x] Add Complete action
- [x] Create UrgentBookingsWidget for dashboard
- [x] Send admin notification on new urgent booking
- [ ] Test urgent booking flow

## Step 8: Event & Training Updates
- [x] Fix Event model: implement HasMedia, add InteractsWithMedia
- [x] Add new Event fields to translatable array
- [x] Add status accessor and scopes (archive, happening, future)
- [x] Update ListEvents action with type filter and new response fields
- [x] Update ShowEvent action with new response fields
- [x] Update EventResource in Filament (new form fields, fix media upload)
- [x] Add Training price field
- [x] Add Training images collection (multiple)
- [x] Add Training reviews relation
- [x] Update ListTrainings action with new response fields
- [x] Update ShowTraining action with reviews
- [x] Create SubmitTrainingReview action + endpoint
- [x] Update TrainingResource in Filament (price, images)
- [x] Add reviews RelationManager to TrainingResource (approve/reject reviews)

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
- [x] Create MediaHelper utility (app/Utils/MediaHelper.php)
- [x] Create lang/en/api.php, lang/ar/api.php, lang/fr/api.php with all API messages
- [x] Update all API Resources to use GetModelMultilangAttribute for ALL translatable fields
- [x] Fix TestimonialResource (currently returns single locale)
- [x] Update all Actions to use MediaHelper for image responses
- [x] Update all Actions to use __() for translated messages
- [x] Create custom Dashboard page (app/Filament/Admin/Pages/Dashboard.php)
- [x] Create StatsOverviewWidget (total patients, today appointments, monthly stats, pending actions)
- [x] Create PendingActionsWidget (pending appointments, cancellation requests, reschedule requests)
- [x] Create TodayAppointmentsWidget (table of today's appointments)
- [x] Create RecentActivityWidget (activity feed)
- [x] Create recent-activity Blade view
- [x] Update AdminPanelProvider (use custom Dashboard, clean up widget registration)
- [x] Set $sort and $columnSpan on existing AppointmentCalendarWidget
- [x] Polish all Filament resources (Events: new fields, Trainings: price/images, Patients: verification status)
- [ ] Final run of all tests
- [x] Verify all API responses follow conventions

---

## Completion Checklist
- [x] All migrations run successfully
- [x] All API endpoints return correct translatable format ({ar, fr, en})
- [x] All API endpoints return images with {original, thumb} format
- [x] All error messages are translatable via lang files
- [x] Dashboard has all widgets and shows correct data
- [x] All Filament resources CRUD works
- [ ] All tests pass
- [ ] Code committed
