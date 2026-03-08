# Step 1: Project Overview & Gap Analysis

## Current State of the Project

### Tech Stack
- **Framework:** Laravel 11+ with Filament v3 admin panel
- **Auth:** Laravel Sanctum (token-based, access + refresh tokens)
- **Scheduling:** Zap (laravel-zap) for doctor availability & blocked schedules
- **Media:** Spatie Media Library
- **Translations:** Spatie Translatable (ar, en, fr)
- **Settings:** Spatie Laravel Settings
- **API Docs:** Scramble (auto-generated)
- **Testing:** PHPUnit (Pest-compatible)

### Existing Models
| Model | Key Fields | Notes |
|-------|-----------|-------|
| `User` | name, email, password | Admin user for Filament panel |
| `Doctor` | name (translatable), specialty (translatable), diplomas, email, phone, address, metadata | Uses HasSchedules (Zap) |
| `Patient` | first_name, last_name, email, phone, age, gender, password | Authenticatable via Sanctum |
| `Service` | name (translatable), price, duration, active, availability | Many-to-many with Doctor |
| `Appointment` | from, to, doctor_id, service_id, patient_id, price, status, metadata | SoftDeletes, Eventable (Guava Calendar) |
| `Event` | title (translatable), description (translatable), date, is_archived, location (translatable) | SoftDeletes |
| `Training` | title (translatable), description (translatable), trainer_name, duration, documents, video_url | SoftDeletes, HasMedia |
| `Testimonial` | patient_id, patient_name, content, rating, is_published | SoftDeletes |

### Existing Enums
- `AppointmentStatus`: pending, confirmed, rejected, cancelled, completed
- `Gender`: male, female
- `ServiceAvailability`: (service availability states)
- `TokenAbility`: (access, refresh token abilities)

### Existing API Routes (v1)
| Method | Route | Auth | Description |
|--------|-------|------|-------------|
| POST | `/patient/auth/register` | No | Register patient |
| POST | `/patient/auth/login` | No | Login patient |
| POST | `/patient/auth/logout` | Yes | Logout patient |
| POST | `/patient/auth/refresh-token` | Yes (refresh) | Refresh access token |
| GET | `/patient/profile/me` | Yes | Get patient profile |
| POST | `/patient/profile/update` | Yes | Update patient profile |
| POST | `/patient/profile/update-password` | Yes | Update patient password |
| GET | `/appointement/doctor` | No | List doctors |
| GET | `/appointement/doctor/{id}` | No | Show doctor |
| GET | `/appointement/service` | No | List services |
| GET | `/appointement/service/{id}` | No | Show service |
| GET | `/appointement/{doctor}/{service}/availability` | No | Get next available slots |
| POST | `/booking/{doctor}/{service}/check-availability` | No | Check specific slot |
| POST | `/booking/{doctor}/{service}/book` | Yes | Book appointment |
| GET | `/services/service` | No | List services (public) |
| GET | `/services/service/{id}` | No | Show service (public) |
| GET | `/events` | No | List events |
| GET | `/events/{id}` | No | Show event |
| GET | `/trainings` | No | List trainings |
| GET | `/trainings/{id}` | No | Show training |
| GET | `/testimonials` | No | List testimonials |
| GET | `/testimonials/{id}` | No | Show testimonial |

### Existing Filament Admin Resources
- `AppointmentResource` (basic - view/list)
- `DoctorResource` (full CRUD with schedule management via Zap)
- `PatientResource`
- `ServiceResource`
- `EventResource`
- `TrainingResource`
- `TestimonialResource`

### Existing Tests
- `ComprehensiveBookingTest` (validation, availability, slots, overlap, edge cases)
- `AppointmentUpdateAvailabilityTest`
- `AppointmentZapSyncTest`
- `BookingApiTest`
- `DoctorAvailabilityTest`

---

## What's Missing (Gap Analysis)

### 1. Patient Appointments API ❌
**No endpoints exist for patients to view, manage, or track their appointments.**
- List patient's own appointments (with filters: status, date range, pagination)
- Show single appointment details
- Cancel an appointment (creates a cancellation request pending admin approval)
- Reschedule an appointment (creates a reschedule request pending admin approval)

### 2. Notification System ❌
**No notification system exists at all.**
- Database notifications table exists (migration present) but unused
- Admin & Doctor: Use Filament's built-in `Notification` class (database channel)
- Patient: Use a custom translatable array-based notification system (stored in DB)
- Notification API for patients to fetch/read/mark-as-read their notifications
- Trigger notifications on: appointment booked, confirmed, rejected, cancelled, rescheduled, urgent booking

### 3. Admin Appointment Validation/Management ❌ (Partial)
**The current Filament `AppointmentResource` is basic. Missing:**
- Approve/reject/confirm pending appointments (with notification dispatch)
- Handle cancellation requests from patients
- Handle reschedule requests from patients
- Full appointment lifecycle management (pending → confirmed → completed or rejected)
- Appointment notes/admin comments
- Filter by status, doctor, patient, date range
- Bulk actions (confirm all pending, etc.)

### 4. Email Verification for Patients ❌
**Patient model has `email_verified_at` cast but no verification flow.**
- `MustVerifyEmail` contract is commented out on User, not on Patient
- Need verification email sending on registration
- Need email verification endpoint
- Need resend verification email endpoint
- Custom Blade email templates (not default Laravel ones)
- Gate/middleware to restrict unverified patients from booking

### 5. Urgent Booking System ❌
**Completely new feature, not related to the current Appointment system.**
- New `UrgentBooking` model with its own logic
- Separate from regular appointments (different table, different flow)
- Admin defines night/urgent slots and max capacity
- Patient/visitor can submit urgent booking request
- Admin gets priority alert/notification
- Admin processes immediately
- Separate API endpoints
- Separate Filament admin page/resource

### 6. Event Model Missing Fields ❌ (from docs/new/events.txt)
**Current Event model is missing several fields specified in the requirements:**
- `time` (separate from date, or use datetime)
- `speakers` (translatable, could be JSON array or relation)
- `about_event` (translatable text, currently using `description`)
- `what_to_expect` (translatable text, new field)
- `pictures` → currently has `gallery` media collection but Event model is NOT implementing `HasMedia`/`InteractsWithMedia` (BROKEN)
- Event categorization: archive / happening / future (currently only `is_archived` boolean)

### 7. Training Model Missing Fields ❌ (from docs/new/training(learning).txt)
**Current Training model is missing:**
- `price` field
- `reviews` (either a relation to a reviews table, or a simple rating/review system)
- `images` (plural - currently only has single `image` collection)

### 8. Comprehensive Test Suite ❌ (Partial)
**Only booking tests exist. Need:**
- Unit tests for all Actions
- Feature tests for all API endpoints (auth, profile, events, trainings, testimonials)
- Feature tests for new appointment management APIs
- Feature tests for notification APIs
- Feature tests for email verification flow
- Feature tests for urgent booking
- Pest Browser tests for Filament admin panel using `pestphp/pest-plugin-browser`

---

## Architecture Notes

### Important: Zap Scheduling System
- Availability is managed through Zap (not directly through Appointment model)
- When an appointment is booked, a Zap blocked schedule is created (via `AppointmentObserver`)
- Doctor availability = Zap availability schedules minus Zap blocked schedules
- Editing appointment times requires Zap block sync (handled by observer)
- The `frequency_config` stores recurrence data (e.g., `{days: ['monday', 'tuesday']}`)
- Time periods are in a separate `schedule_periods` table

### Important: Translatable Fields
- Doctor: name, specialty
- Service: name
- Event: title, description, location
- Training: title, description
- All new translatable fields must support: ar, en, fr
- Patient notifications must be translatable (store as array with locale keys)

### Important: Action Pattern
The project uses the Action pattern extensively:
- `app/Actions/Patient/Auth/` - authentication actions
- `app/Actions/Patient/Booking/` - booking actions
- `app/Actions/Doctor/` - doctor schedule management actions
- `app/Actions/Event/` - event actions
- `app/Actions/Training/` - training actions
- `app/Actions/Testimonial/` - testimonial actions
- **All new business logic should follow this pattern**
