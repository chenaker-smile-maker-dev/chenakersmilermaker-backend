# Step 4: Notification System

Three different notification targets with different mechanisms:

- **Admin & Doctor:** Filament database notifications (using Laravel's `Notification` class and Filament's built-in notification UI)
- **Patient:** Custom `patient_notifications` table with translatable content (JSON arrays for ar/en/fr)

---

## 4.1 — Admin & Doctor Notifications (Filament)

### How It Works

Filament v3 has built-in support for database notifications. The `User` model already uses `Notifiable`. Notifications sent via `DatabaseChannel` appear in the Filament bell icon dropdown automatically.

### Notification Classes to Create

**Directory:** `app/Notifications/Admin/`

| Class | Trigger | Content |
|-------|---------|---------|
| `NewAppointmentBooked` | Patient books an appointment | "New appointment: {patient_name} with Dr. {doctor_name} on {date} at {time}" |
| `AppointmentCancellationRequested` | Patient requests cancellation | "Cancellation request: Appointment #{id} - {patient_name} - Reason: {reason}" |
| `AppointmentRescheduleRequested` | Patient requests reschedule | "Reschedule request: Appointment #{id} - {patient_name} wants to move to {new_date} {new_time}" |
| `NewUrgentBookingReceived` | Urgent booking submitted | "🚨 URGENT: {patient_name} - {phone} - Reason: {reason}" |
| `NewTestimonialSubmitted` | Patient submits testimonial | "New testimonial from {patient_name} - Rating: {rating}/5" |

### Implementation Pattern

```php
<?php

namespace App\Notifications\Admin;

use App\Models\Appointment;
use Illuminate\Notifications\Notification;
use Filament\Notifications\Notification as FilamentNotification;

class NewAppointmentBooked extends Notification
{
    public function __construct(private Appointment $appointment) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return FilamentNotification::make()
            ->title('New Appointment Booked')
            ->icon('heroicon-o-calendar')
            ->body("Patient {$this->appointment->patient->full_name} booked with Dr. {$this->appointment->doctor->display_name} on {$this->appointment->from->format('M d, Y')} at {$this->appointment->from->format('H:i')}")
            ->actions([
                \Filament\Notifications\Actions\Action::make('view')
                    ->label('View Appointment')
                    ->url(route('filament.admin.resources.appointments.view', $this->appointment->id))
                    ->markAsRead(),
            ])
            ->getDatabaseMessage();
    }
}
```

### Dispatching Admin Notifications

Send to all admin users (or a specific admin):

```php
use App\Models\User;

// Send to all admins
$admins = User::all(); // or filter by role if using Spatie Permissions
Notification::send($admins, new NewAppointmentBooked($appointment));
```

### Ensure User Model Has Notifiable

The `User` model already uses `Notifiable`. Verify the Filament panel has `->databaseNotifications()` enabled in the PanelProvider.

**Check:** `app/Providers/Filament/AdminPanelProvider.php` must include:
```php
->databaseNotifications()
->databaseNotificationsPolling('30s') // optional: auto-refresh every 30s
```

---

## 4.2 — Patient Notifications (Custom Table)

### Why Not Laravel's Notification System?

Patient notifications need:
1. **Translatable title and body** (ar, en, fr stored as JSON arrays)
2. **Simple REST API** for mobile/frontend consumption
3. **No polymorphic complexity** — patients are the only target

### PatientNotification Service

**File:** `app/Services/PatientNotificationService.php`

```php
<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\PatientNotification;

class PatientNotificationService
{
    /**
     * Send a notification to a patient.
     *
     * @param Patient $patient
     * @param string $type          e.g., 'appointment_confirmed'
     * @param array  $title         ['en' => '...', 'ar' => '...', 'fr' => '...']
     * @param array  $body          ['en' => '...', 'ar' => '...', 'fr' => '...']
     * @param array  $data          optional extra payload
     * @param string|null $actionUrl optional deep link
     */
    public static function send(
        Patient $patient,
        string $type,
        array $title,
        array $body,
        array $data = [],
        ?string $actionUrl = null,
    ): PatientNotification {
        return PatientNotification::create([
            'patient_id' => $patient->id,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'data' => $data,
            'action_url' => $actionUrl,
        ]);
    }
}
```

### Notification Type Constants

**File:** `app/Enums/PatientNotificationType.php`

```php
enum PatientNotificationType: string
{
    case APPOINTMENT_BOOKED = 'appointment_booked';
    case APPOINTMENT_CONFIRMED = 'appointment_confirmed';
    case APPOINTMENT_REJECTED = 'appointment_rejected';
    case APPOINTMENT_CANCELLED = 'appointment_cancelled';
    case APPOINTMENT_RESCHEDULED = 'appointment_rescheduled';
    case CANCELLATION_APPROVED = 'cancellation_approved';
    case CANCELLATION_REJECTED = 'cancellation_rejected';
    case RESCHEDULE_APPROVED = 'reschedule_approved';
    case RESCHEDULE_REJECTED = 'reschedule_rejected';
    case URGENT_BOOKING_ACCEPTED = 'urgent_booking_accepted';
    case URGENT_BOOKING_REJECTED = 'urgent_booking_rejected';
    case EMAIL_VERIFIED = 'email_verified';
    case GENERAL = 'general';
}
```

### Translatable Notification Templates

**File:** `app/Services/PatientNotificationTemplates.php`

Define all notification templates with translations:

```php
class PatientNotificationTemplates
{
    public static function appointmentConfirmed(string $doctorName, string $date, string $time): array
    {
        return [
            'title' => [
                'en' => 'Appointment Confirmed',
                'ar' => 'تم تأكيد الموعد',
                'fr' => 'Rendez-vous confirmé',
            ],
            'body' => [
                'en' => "Your appointment with {$doctorName} on {$date} at {$time} has been confirmed.",
                'ar' => "تم تأكيد موعدك مع {$doctorName} بتاريخ {$date} الساعة {$time}.",
                'fr' => "Votre rendez-vous avec {$doctorName} le {$date} à {$time} a été confirmé.",
            ],
        ];
    }

    // ... similar methods for each notification type
    // appointmentRejected, appointmentCancelled, appointmentRescheduled, etc.
}
```

---

## 4.3 — Patient Notification API

### New Routes

Add to `routes/api/v1.php` inside the patient prefix, authenticated:

```php
Route::prefix('patient')->group(function () {
    Route::prefix('notifications')
        ->middleware(['auth:sanctum', 'access'])
        ->group(function () {
            Route::get('/', [PatientNotificationController::class, 'index']);
            Route::get('/unread-count', [PatientNotificationController::class, 'unreadCount']);
            Route::post('/{notification}/read', [PatientNotificationController::class, 'markAsRead']);
            Route::post('/read-all', [PatientNotificationController::class, 'markAllAsRead']);
            Route::delete('/{notification}', [PatientNotificationController::class, 'destroy']);
        });
});
```

### Endpoint Specifications

#### GET `/api/v1/patient/notifications`

**List patient's notifications with pagination.**

**Query Parameters:**
| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `page` | int | 1 | Pagination page |
| `per_page` | int | 15 | Items per page |
| `unread_only` | bool | false | Only show unread notifications |

**Response (200):**
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": "uuid-1",
        "type": "appointment_confirmed",
        "title": "Appointment Confirmed",
        "body": "Your appointment with Dr. Ahmed on Mar 15 at 14:30 has been confirmed.",
        "data": { "appointment_id": 42 },
        "action_url": null,
        "is_read": false,
        "created_at": "2026-03-02T09:00:00Z"
      }
    ],
    "pagination": { ... }
  }
}
```

**Note:** `title` and `body` are returned in the current request locale (from `Accept-Language` header or app locale). The raw translatable arrays are stored in DB but the API returns the localized string.

#### GET `/api/v1/patient/notifications/unread-count`

```json
{
  "success": true,
  "data": { "unread_count": 3 }
}
```

#### POST `/api/v1/patient/notifications/{notification}/read`

Mark single notification as read. Returns 200.

#### POST `/api/v1/patient/notifications/read-all`

Mark all notifications as read for the authenticated patient. Returns 200.

#### DELETE `/api/v1/patient/notifications/{notification}`

Soft-delete a notification. Returns 200.

---

## 4.4 — When to Dispatch Notifications

### Notification Triggers

| Event | Admin Notification | Patient Notification |
|-------|-------------------|---------------------|
| Patient books appointment | ✅ `NewAppointmentBooked` | ✅ `appointment_booked` |
| Admin confirms appointment | ❌ | ✅ `appointment_confirmed` |
| Admin rejects appointment | ❌ | ✅ `appointment_rejected` |
| Patient requests cancellation | ✅ `AppointmentCancellationRequested` | ❌ (patient initiated) |
| Admin approves cancellation | ❌ | ✅ `cancellation_approved` |
| Admin rejects cancellation | ❌ | ✅ `cancellation_rejected` |
| Patient requests reschedule | ✅ `AppointmentRescheduleRequested` | ❌ (patient initiated) |
| Admin approves reschedule | ❌ | ✅ `reschedule_approved` |
| Admin rejects reschedule | ❌ | ✅ `reschedule_rejected` |
| Urgent booking submitted | ✅ `NewUrgentBookingReceived` | ✅ `urgent_booking_received` (if logged in) |
| Admin accepts urgent booking | ❌ | ✅ `urgent_booking_accepted` |
| Admin rejects urgent booking | ❌ | ✅ `urgent_booking_rejected` |

### Where to Dispatch

Notifications should be dispatched **inside the Action classes** or via **model observers**, not in controllers.

Recommended: Dispatch in the Action classes that handle each operation, e.g.:
- `CreateAppointment` action → sends `NewAppointmentBooked` to admin + `appointment_booked` to patient
- Admin Filament action for "Confirm Appointment" → sends `appointment_confirmed` to patient
- `RequestAppointmentCancellation` action → sends `AppointmentCancellationRequested` to admin

---

## 4.5 — Files to Create

| File | Description |
|------|-------------|
| `app/Http/Controllers/Api/V1/PatientNotificationController.php` | API controller |
| `app/Services/PatientNotificationService.php` | Service to create patient notifications |
| `app/Services/PatientNotificationTemplates.php` | Translatable templates |
| `app/Enums/PatientNotificationType.php` | Notification type enum |
| `app/Notifications/Admin/NewAppointmentBooked.php` | Filament notification |
| `app/Notifications/Admin/AppointmentCancellationRequested.php` | Filament notification |
| `app/Notifications/Admin/AppointmentRescheduleRequested.php` | Filament notification |
| `app/Notifications/Admin/NewUrgentBookingReceived.php` | Filament notification |
| `app/Notifications/Admin/NewTestimonialSubmitted.php` | Filament notification |
| `app/Actions/Patient/Notification/ListPatientNotifications.php` | Action |
| `app/Actions/Patient/Notification/MarkNotificationAsRead.php` | Action |
