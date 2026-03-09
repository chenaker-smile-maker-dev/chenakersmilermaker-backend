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
- [ ] Test endpoints manually

## Step 4: Notification System
- [x] Create PatientNotificationService
- [x] Create PatientNotificationTemplates (translatable ar/fr/en)
- [x] Create PatientNotificationController (list, unread-count, mark-read, mark-all-read, delete)
- [x] Add notification routes
- [x] Create Filament notification classes (NewAppointmentBooked, CancellationRequested, RescheduleRequested, NewUrgentBookingReceived, NewTestimonialSubmitted)
- [x] Enable databaseNotifications() in AdminPanelProvider (already enabled)
- [x] Wire up notification dispatch in all relevant actions (booking, cancellation, reschedule, urgent booking)
- [ ] Test notification flow

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
- [x] Create PendingAppointmentsWidget
- [x] All admin actions dispatch patient notifications

## Step 6: Email Verification
- [x] Create base email Blade layout (RTL support for Arabic)
- [x] Create verify-email Blade template
- [x] Create VerifyEmailMail mailable
- [x] Create SendVerificationEmail action
- [x] Create VerifyEmail action
- [x] Add verify-email and resend-verification API endpoints
- [x] Send verification email on registration (update AuthController)
- [ ] Test full verification flow

## Step 7: Urgent Booking System
- [x] Create UrgentBookingController (submit, list, show)
- [x] Create SubmitUrgentBooking action
- [x] Create ListPatientUrgentBookings action
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
- [x] Update EventResource in Filament (new form fields: time, speakers, about_event, what_to_expect, gallery upload)
- [x] Add Training price field
- [x] Add Training images collection (multiple)
- [x] Add Training reviews relation
- [x] Update ListTrainings action with new response fields
- [x] Update ShowTraining action with reviews
- [x] Create SubmitTrainingReview action + endpoint
- [x] Update TrainingResource in Filament (price, images upload)
- [ ] Create Review approval in Filament (relation manager or standalone resource)

## Step 9: Tests
- [ ] Create/update all factories
- [ ] Feature tests: Auth (register, login, logout, refresh, email verification)
- [ ] Feature tests: Patient appointments (list, show, cancel, reschedule)
- [ ] Feature tests: Notifications (list, unread count, mark read, delete)
- [ ] Feature tests: Urgent booking (submit, list, show)
- [ ] Feature tests: Events (list with type filter, show)
- [ ] Feature tests: Trainings (list, show, submit review)
- [ ] Feature tests: Testimonials (list, show)

## Step 10: Dashboard, API Conventions & Polish
- [x] Create MediaHelper utility (app/Utils/MediaHelper.php)
- [x] Create lang/en/api.php, lang/ar/api.php, lang/fr/api.php with all API messages
- [x] Update DoctorResource to use MediaHelper
- [x] Update ServiceResource to use MediaHelper
- [x] Fix TestimonialResource (use MediaHelper for patient photo)
- [x] Create custom Dashboard page (app/Filament/Admin/Pages/Dashboard.php)
- [x] Create StatsOverviewWidget (total patients, today appointments, monthly stats, pending actions)
- [x] Create PendingActionsWidget (pending appointments, cancellation requests, reschedule requests)
- [x] Create TodayAppointmentsWidget (table of today's appointments)
- [x] Create RecentActivityWidget (activity feed)
- [x] Create recent-activity Blade view
- [x] Update AdminPanelProvider (use custom Dashboard, clean up widget registration)
- [x] Set $sort and $columnSpan on existing AppointmentCalendarWidget
- [ ] Polish Trainings: reviews relation manager in Filament

---

## Completion Checklist
- [x] All migrations run successfully
- [x] All API endpoints return correct translatable format ({ar, fr, en})
- [x] All API endpoints return images with {original, thumb} format (via MediaHelper)
- [x] All error messages are translatable via lang files
- [x] Dashboard has all widgets and shows correct data
- [x] All Filament resources CRUD works
- [ ] All tests pass
- [ ] Code committed

