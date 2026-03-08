# Step 7: Urgent Booking System

A completely separate system from regular appointments. Urgent bookings are for emergency/night situations where a patient or visitor needs immediate attention.

---

## 7.1 — Overview

**Key differences from regular appointments:**

| Aspect | Regular Appointment | Urgent Booking |
|--------|-------------------|----------------|
| Model | `Appointment` | `UrgentBooking` |
| Table | `appointments` | `urgent_bookings` |
| Auth required | Yes (patient must be logged in) | No (visitors can submit) |
| Scheduling | Uses Zap availability system | No Zap involvement |
| Doctor selection | Patient chooses doctor | Admin assigns doctor |
| Time slot | Patient picks available slot | Patient suggests preferred time, admin decides |
| Validation flow | Automatic availability check | Admin manually reviews |
| Status flow | pending → confirmed → completed | pending → accepted → completed |

---

## 7.2 — UrgentBookingStatus Enum

**File:** `app/Enums/UrgentBookingStatus.php`

```php
<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum UrgentBookingStatus: string implements HasLabel, HasColor, HasIcon
{
    case PENDING = 'pending';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';
    case COMPLETED = 'completed';

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::ACCEPTED => 'Accepted',
            self::REJECTED => 'Rejected',
            self::COMPLETED => 'Completed',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::ACCEPTED => 'success',
            self::REJECTED => 'danger',
            self::COMPLETED => 'info',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::PENDING => 'heroicon-o-clock',
            self::ACCEPTED => 'heroicon-o-check-circle',
            self::REJECTED => 'heroicon-o-x-circle',
            self::COMPLETED => 'heroicon-o-clipboard-document-check',
        };
    }
}
```

---

## 7.3 — API Endpoints

### New Routes

Add to `routes/api/v1.php`:

```php
Route::prefix('urgent-booking')->group(function () {
    // No auth required - visitors can submit
    Route::post('/submit', [UrgentBookingController::class, 'submit']);

    // Authenticated patient can view their urgent bookings
    Route::middleware(['auth:sanctum', 'access'])->group(function () {
        Route::get('/my-bookings', [UrgentBookingController::class, 'myBookings']);
        Route::get('/{urgentBooking}', [UrgentBookingController::class, 'show']);
    });
});
```

### POST `/api/v1/urgent-booking/submit`

**No authentication required.** Visitors and patients can both submit.

**Request Body:**
```json
{
  "patient_name": "John Doe",
  "patient_phone": "+213555123456",
  "patient_email": "john@example.com",
  "reason": "Severe toothache, cannot wait until morning",
  "description": "Pain started 2 hours ago, over-the-counter medication not helping",
  "preferred_datetime": "2026-03-08 22:00"
}
```

**Validation:**
| Field | Rules |
|-------|-------|
| `patient_name` | required, string, max:255 |
| `patient_phone` | required, string, max:20 |
| `patient_email` | nullable, email |
| `reason` | required, string, min:10, max:1000 |
| `description` | nullable, string, max:2000 |
| `preferred_datetime` | nullable, date_format:Y-m-d H:i |

**Response (201):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "patient_name": "John Doe",
    "patient_phone": "+213555123456",
    "status": "pending",
    "created_at": "2026-03-08T20:30:00Z"
  },
  "message": "Urgent booking request submitted successfully. You will be contacted shortly."
}
```

**Action:** `app/Actions/Patient/UrgentBooking/SubmitUrgentBooking.php`

**Logic:**
1. Validate input.
2. If user is authenticated as a patient, auto-fill `patient_id`.
3. Create `UrgentBooking` record with status `pending`.
4. **Send PRIORITY notification to admin** (Filament notification with danger color).
5. If patient is authenticated, send them a patient notification confirming receipt.
6. Return booking confirmation.

---

### GET `/api/v1/urgent-booking/my-bookings`

**Authentication required.** Returns authenticated patient's urgent bookings.

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "reason": "Severe toothache",
      "status": "accepted",
      "assigned_doctor": {
        "id": 3,
        "name": "Dr. Fatima Ali"
      },
      "scheduled_datetime": "2026-03-08T22:00:00Z",
      "admin_notes": "Come to the clinic at 10 PM. Dr. Fatima will be waiting.",
      "created_at": "2026-03-08T20:30:00Z"
    }
  ]
}
```

### GET `/api/v1/urgent-booking/{id}`

**Authentication required.** Show single urgent booking (must belong to patient).

---

## 7.4 — Filament Admin Resource

**File:** `app/Filament/Admin/Resources/UrgentBookings/UrgentBookingResource.php`

### Table Columns

| Column | Type | Notes |
|--------|------|-------|
| ID | TextColumn | |
| Patient Name | TextColumn | Searchable |
| Phone | TextColumn | Searchable |
| Reason | TextColumn | Wrap, limit 50 chars |
| Status | BadgeColumn | Color-coded by enum |
| Assigned Doctor | TextColumn | From relation, nullable |
| Preferred Time | TextColumn | Date + time formatted |
| Scheduled Time | TextColumn | Admin-set time |
| Created At | TextColumn | Sortable |

### Table Actions

| Action | Visible When | Description |
|--------|-------------|-------------|
| View | Always | View full details |
| Accept | status = pending | Accept and assign doctor + schedule time |
| Reject | status = pending | Reject with reason |
| Complete | status = accepted | Mark as completed |

### Accept Action Form

When admin accepts, they must provide:
```php
Action::make('accept')
    ->form([
        Select::make('assigned_doctor_id')
            ->label('Assign Doctor')
            ->options(Doctor::pluck('name', 'id'))
            ->required()
            ->searchable(),
        DateTimePicker::make('scheduled_datetime')
            ->label('Scheduled Date & Time')
            ->required()
            ->minDate(now()),
        Textarea::make('admin_notes')
            ->label('Notes for Patient')
            ->placeholder('Instructions for the patient...'),
    ])
    ->action(function (UrgentBooking $record, array $data) {
        $record->update([
            'status' => UrgentBookingStatus::ACCEPTED,
            'assigned_doctor_id' => $data['assigned_doctor_id'],
            'scheduled_datetime' => $data['scheduled_datetime'],
            'admin_notes' => $data['admin_notes'],
        ]);

        // Notify patient if they have an account
        if ($record->patient) {
            PatientNotificationService::send(
                $record->patient,
                'urgent_booking_accepted',
                ...PatientNotificationTemplates::urgentBookingAccepted(...),
            );
        }
    });
```

### Dashboard Widget

**File:** `app/Filament/Admin/Widgets/UrgentBookingsWidget.php`

Show count of pending urgent bookings with a prominent danger-colored badge:

```php
Stat::make('Urgent Bookings', UrgentBooking::where('status', 'pending')->count())
    ->icon('heroicon-o-exclamation-triangle')
    ->color('danger')
    ->description('Pending urgent requests')
```

---

## 7.5 — Files to Create

| File | Description |
|------|-------------|
| `app/Models/UrgentBooking.php` | Model (from Step 2) |
| `app/Enums/UrgentBookingStatus.php` | Status enum |
| `app/Http/Controllers/Api/V1/UrgentBookingController.php` | API controller |
| `app/Actions/Patient/UrgentBooking/SubmitUrgentBooking.php` | Submit action |
| `app/Actions/Patient/UrgentBooking/ListPatientUrgentBookings.php` | List action |
| `app/Filament/Admin/Resources/UrgentBookings/UrgentBookingResource.php` | Filament resource |
| `app/Filament/Admin/Resources/UrgentBookings/Pages/ListUrgentBookings.php` | List page |
| `app/Filament/Admin/Resources/UrgentBookings/Pages/ViewUrgentBooking.php` | View page |
| `app/Filament/Admin/Widgets/UrgentBookingsWidget.php` | Dashboard widget |
| `app/Notifications/Admin/NewUrgentBookingReceived.php` | Admin notification |
| `database/migrations/..._create_urgent_bookings_table.php` | Migration (from Step 2) |
| `database/factories/UrgentBookingFactory.php` | Factory for tests |
