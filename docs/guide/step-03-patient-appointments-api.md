# Step 3: Patient Appointments API

Patients need endpoints to view and manage their own appointments.

---

## 3.1 — New Controller

**File:** `app/Http/Controllers/Api/V1/PatientAppointmentController.php`

This controller handles all patient-facing appointment operations.

---

## 3.2 — New Routes

Add to `routes/api/v1.php` inside the `patient` prefix group, **all authenticated** with `auth:sanctum` and `access` middleware:

```php
Route::prefix('patient')->group(function () {
    // ... existing auth & profile routes ...

    Route::prefix('appointments')
        ->middleware(['auth:sanctum', 'access'])
        ->group(function () {
            Route::get('/', [PatientAppointmentController::class, 'index']);
            Route::get('/{appointment}', [PatientAppointmentController::class, 'show']);
            Route::post('/{appointment}/cancel', [PatientAppointmentController::class, 'requestCancellation']);
            Route::post('/{appointment}/reschedule', [PatientAppointmentController::class, 'requestReschedule']);
        });
});
```

---

## 3.3 — Endpoint Specifications

### GET `/api/v1/patient/appointments`

**List patient's appointments with filtering and pagination.**

**Query Parameters:**
| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `status` | string | null | Filter by status: pending, confirmed, rejected, cancelled, completed |
| `from_date` | string (Y-m-d) | null | Filter appointments from this date |
| `to_date` | string (Y-m-d) | null | Filter appointments up to this date |
| `page` | int | 1 | Pagination page |
| `per_page` | int | 10 | Items per page |

**Response (200):**
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 42,
        "doctor": {
          "id": 1,
          "name": "Dr. Ahmed Hassan",
          "specialty": "Dentist",
          "image": "https://..."
        },
        "service": {
          "id": 1,
          "name": "Teeth Cleaning",
          "price": 250,
          "duration": 30
        },
        "date": "2026-03-15",
        "start_time": "14:30",
        "end_time": "15:00",
        "status": "confirmed",
        "change_request_status": null,
        "price": 250,
        "created_at": "2026-03-01T12:00:00Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 10,
      "total": 5,
      "last_page": 1
    }
  }
}
```

**Action:** `app/Actions/Patient/Appointment/ListPatientAppointments.php`

**Logic:**
1. Get authenticated patient.
2. Query appointments where `patient_id = patient.id`.
3. Apply optional filters (status, date range).
4. Order by `from` descending (most recent first).
5. Paginate.
6. Transform using a resource/manual mapping (include doctor name, service name, image URLs).

---

### GET `/api/v1/patient/appointments/{appointment}`

**Show single appointment details.**

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 42,
    "doctor": {
      "id": 1,
      "name": "Dr. Ahmed Hassan",
      "specialty": "Dentist",
      "image": "https://...",
      "phone": "201234567890"
    },
    "service": {
      "id": 1,
      "name": "Teeth Cleaning",
      "price": 250,
      "duration": 30,
      "image": "https://..."
    },
    "date": "2026-03-15",
    "start_time": "14:30",
    "end_time": "15:00",
    "status": "confirmed",
    "change_request_status": null,
    "price": 250,
    "admin_notes": null,
    "cancellation_reason": null,
    "reschedule_reason": null,
    "created_at": "2026-03-01T12:00:00Z",
    "confirmed_at": "2026-03-02T09:00:00Z"
  }
}
```

**Action:** `app/Actions/Patient/Appointment/ShowPatientAppointment.php`

**Logic:**
1. Find appointment by ID.
2. Ensure `appointment.patient_id === auth patient id` (authorization).
3. Return detailed appointment with doctor and service relations loaded.

**Error:** 403 if not the patient's own appointment, 404 if not found.

---

### POST `/api/v1/patient/appointments/{appointment}/cancel`

**Request cancellation (admin must approve).**

**Request Body:**
```json
{
  "reason": "I am traveling and cannot make the appointment"
}
```

**Validation:**
- `reason`: required, string, min:5, max:500

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 42,
    "status": "confirmed",
    "change_request_status": "pending_cancellation"
  },
  "message": "Cancellation request submitted successfully. Awaiting admin approval."
}
```

**Action:** `app/Actions/Patient/Appointment/RequestAppointmentCancellation.php`

**Logic:**
1. Verify appointment belongs to authenticated patient.
2. Verify appointment status is `pending` or `confirmed` (can't cancel already cancelled/completed/rejected).
3. Verify no other pending change request exists (`change_request_status` must be null).
4. Set `change_request_status = 'pending_cancellation'`.
5. Set `cancellation_reason = reason`.
6. **Dispatch notification to admin** (via Filament database notification on User model).
7. Return updated appointment.

**Errors:**
- 403: Not patient's appointment
- 422: Invalid status for cancellation, or existing pending request

---

### POST `/api/v1/patient/appointments/{appointment}/reschedule`

**Request reschedule (admin must approve).**

**Request Body:**
```json
{
  "reason": "Conflict with work schedule",
  "new_date": "20-03-2026",
  "new_start_time": "10:00"
}
```

**Validation:**
- `reason`: required, string, min:5, max:500
- `new_date`: required, date_format:d-m-Y, after_or_equal:today
- `new_start_time`: required, date_format:H:i

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 42,
    "status": "confirmed",
    "change_request_status": "pending_reschedule",
    "requested_new_date": "2026-03-20",
    "requested_new_time": "10:00"
  },
  "message": "Reschedule request submitted successfully. Awaiting admin approval."
}
```

**Action:** `app/Actions/Patient/Appointment/RequestAppointmentReschedule.php`

**Logic:**
1. Verify appointment belongs to authenticated patient.
2. Verify appointment status is `pending` or `confirmed`.
3. Verify no other pending change request exists.
4. **Check availability** of the new time slot using `CheckAvailabilitySlot` action (the new slot must be available).
5. Store `original_from` and `original_to` (current values, for tracking).
6. Store `requested_new_from` and `requested_new_to` (calculated from new_date + new_start_time + service duration).
7. Set `change_request_status = 'pending_reschedule'`.
8. Set `reschedule_reason = reason`.
9. **Dispatch notification to admin**.
10. Return updated appointment.

**Errors:**
- 403: Not patient's appointment
- 422: Invalid status, existing pending request, or new slot not available

---

## 3.4 — Actions to Create

| Action | File |
|--------|------|
| `ListPatientAppointments` | `app/Actions/Patient/Appointment/ListPatientAppointments.php` |
| `ShowPatientAppointment` | `app/Actions/Patient/Appointment/ShowPatientAppointment.php` |
| `RequestAppointmentCancellation` | `app/Actions/Patient/Appointment/RequestAppointmentCancellation.php` |
| `RequestAppointmentReschedule` | `app/Actions/Patient/Appointment/RequestAppointmentReschedule.php` |

---

## 3.5 — Authorization

Add a policy or use inline checks to ensure patients can only access their own appointments:

```php
// In each action, verify ownership:
if ($appointment->patient_id !== $patient->id) {
    throw new AuthorizationException('This appointment does not belong to you.');
}
```

Consider creating `AppointmentPolicy` with:
- `view(Patient $patient, Appointment $appointment)` → `$appointment->patient_id === $patient->id`
- `cancel(Patient $patient, Appointment $appointment)` → same + status check
- `reschedule(Patient $patient, Appointment $appointment)` → same + status check
