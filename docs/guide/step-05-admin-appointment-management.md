# Step 5: Admin Appointment Management (Filament)

The existing `AppointmentResource` in Filament is basic. This step upgrades it to a full appointment lifecycle management system.

---

## 5.1 — Upgrade AppointmentResource

**File:** `app/Filament/Admin/Resources/Appointments/AppointmentResource.php`

### Table Columns

The appointments list table should display:

| Column | Type | Notes |
|--------|------|-------|
| ID | TextColumn | Sortable |
| Patient Name | TextColumn | From `patient.full_name` relation |
| Doctor Name | TextColumn | From `doctor.display_name` relation, searchable |
| Service | TextColumn | From `service.name` relation |
| Date | TextColumn | Format `from` as `M d, Y` |
| Time | TextColumn | Format `from` as `H:i` - `to` as `H:i` |
| Status | BadgeColumn | Using `AppointmentStatus` enum colors/labels |
| Change Request | BadgeColumn | `change_request_status`, nullable, color-coded |
| Price | TextColumn | Formatted as currency |
| Created At | TextColumn | Sortable, toggleable |

### Table Filters

| Filter | Type | Description |
|--------|------|-------------|
| Status | SelectFilter | Filter by `AppointmentStatus` values |
| Change Request | SelectFilter | pending_cancellation, pending_reschedule |
| Doctor | SelectFilter | Filter by doctor_id (with relationship) |
| Date Range | Filter | Custom date range filter on `from` column |
| Has Change Request | TernaryFilter | Show only appointments with pending requests |

### Table Actions (Per Row)

| Action | Icon | Visible When | Description |
|--------|------|-------------|-------------|
| View | eye | Always | View appointment details |
| Confirm | check-circle | status = pending | Set status to confirmed, notify patient |
| Reject | x-circle | status = pending | Set status to rejected, notify patient (with optional reason) |
| Complete | clipboard-check | status = confirmed | Set status to completed |
| Approve Cancellation | check | change_request_status = pending_cancellation | Approve patient's cancellation request |
| Reject Cancellation | x-mark | change_request_status = pending_cancellation | Reject patient's cancellation request |
| Approve Reschedule | check | change_request_status = pending_reschedule | Approve & apply reschedule |
| Reject Reschedule | x-mark | change_request_status = pending_reschedule | Reject reschedule request |

### Bulk Actions

| Action | Description |
|--------|-------------|
| Confirm Selected | Bulk confirm pending appointments |
| Reject Selected | Bulk reject pending appointments |

---

## 5.2 — Action Implementations

### Confirm Appointment Action

```php
Action::make('confirm')
    ->label('Confirm')
    ->icon('heroicon-o-check-circle')
    ->color('success')
    ->visible(fn (Appointment $record) => $record->status === AppointmentStatus::PENDING)
    ->requiresConfirmation()
    ->action(function (Appointment $record) {
        $record->update([
            'status' => AppointmentStatus::CONFIRMED,
            'confirmed_by' => auth()->id(),
            'confirmed_at' => now(),
        ]);

        // Send patient notification
        PatientNotificationService::send(
            $record->patient,
            PatientNotificationType::APPOINTMENT_CONFIRMED->value,
            ...PatientNotificationTemplates::appointmentConfirmed(
                $record->doctor->display_name,
                $record->from->format('M d, Y'),
                $record->from->format('H:i'),
            ),
            data: ['appointment_id' => $record->id],
        );

        Notification::make()->success()->title('Appointment Confirmed')->send();
    });
```

### Reject Appointment Action

```php
Action::make('reject')
    ->label('Reject')
    ->icon('heroicon-o-x-circle')
    ->color('danger')
    ->visible(fn (Appointment $record) => $record->status === AppointmentStatus::PENDING)
    ->form([
        Textarea::make('admin_notes')
            ->label('Reason for rejection')
            ->required(),
    ])
    ->action(function (Appointment $record, array $data) {
        $record->update([
            'status' => AppointmentStatus::REJECTED,
            'admin_notes' => $data['admin_notes'],
        ]);

        // Remove Zap block since appointment is rejected
        // (handle in observer or here)

        // Send patient notification
        PatientNotificationService::send(
            $record->patient,
            PatientNotificationType::APPOINTMENT_REJECTED->value,
            ...PatientNotificationTemplates::appointmentRejected(
                $record->doctor->display_name,
                $record->from->format('M d, Y'),
                $record->from->format('H:i'),
                $data['admin_notes'],
            ),
            data: ['appointment_id' => $record->id],
        );

        Notification::make()->success()->title('Appointment Rejected')->send();
    });
```

### Approve Cancellation Action

```php
Action::make('approve_cancellation')
    ->label('Approve Cancellation')
    ->icon('heroicon-o-check')
    ->color('warning')
    ->visible(fn (Appointment $record) => $record->change_request_status === 'pending_cancellation')
    ->requiresConfirmation()
    ->action(function (Appointment $record) {
        $record->update([
            'status' => AppointmentStatus::CANCELLED,
            'change_request_status' => 'approved',
        ]);

        // Remove Zap block for this time slot
        // ... Zap sync logic ...

        // Send patient notification
        PatientNotificationService::send(
            $record->patient,
            PatientNotificationType::CANCELLATION_APPROVED->value,
            ...PatientNotificationTemplates::cancellationApproved(
                $record->doctor->display_name,
                $record->from->format('M d, Y'),
            ),
            data: ['appointment_id' => $record->id],
        );

        Notification::make()->success()->title('Cancellation Approved')->send();
    });
```

### Approve Reschedule Action

```php
Action::make('approve_reschedule')
    ->label('Approve Reschedule')
    ->icon('heroicon-o-check')
    ->color('info')
    ->visible(fn (Appointment $record) => $record->change_request_status === 'pending_reschedule')
    ->requiresConfirmation()
    ->action(function (Appointment $record) {
        // Move appointment to new time
        $record->update([
            'from' => $record->requested_new_from,
            'to' => $record->requested_new_to,
            'change_request_status' => 'approved',
            'reschedule_reason' => null, // clear after processing
        ]);

        // Zap: remove old block, create new block (observer handles this)

        // Send patient notification
        PatientNotificationService::send(
            $record->patient,
            PatientNotificationType::RESCHEDULE_APPROVED->value,
            ...PatientNotificationTemplates::rescheduleApproved(
                $record->doctor->display_name,
                $record->from->format('M d, Y'),
                $record->from->format('H:i'),
            ),
            data: ['appointment_id' => $record->id],
        );

        Notification::make()->success()->title('Reschedule Approved')->send();
    });
```

---

## 5.3 — View Page Enhancements

**File:** `app/Filament/Admin/Resources/Appointments/Pages/ViewAppointment.php`

The view page should show:

### Info Section
- Patient details (name, phone, email, age, gender)
- Doctor details (name, specialty)
- Service details (name, price, duration)
- Appointment time (date, from, to)
- Status badge
- Price

### Change Request Section (visible only when change_request_status is not null)
- Request type (cancellation / reschedule)
- Patient's reason
- If reschedule: requested new date/time
- Original date/time (for reschedules)
- Action buttons (approve / reject)

### Admin Notes Section
- Editable textarea for admin notes
- History of status changes (if you add an activity log)

### Timeline / Activity Log (optional but recommended)
- Use `spatie/laravel-activitylog` (already installed) to show appointment history

---

## 5.4 — Dashboard Widget

**File:** `app/Filament/Admin/Widgets/PendingAppointmentsWidget.php`

A widget on the admin dashboard showing:
- Count of pending appointments
- Count of pending cancellation requests
- Count of pending reschedule requests
- Quick links to filtered views

```php
class PendingAppointmentsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Pending Appointments', Appointment::where('status', 'pending')->count())
                ->icon('heroicon-o-clock')
                ->color('warning')
                ->url(route('filament.admin.resources.appointments.index', ['tableFilters[status][value]' => 'pending'])),

            Stat::make('Cancellation Requests', Appointment::where('change_request_status', 'pending_cancellation')->count())
                ->icon('heroicon-o-x-circle')
                ->color('danger'),

            Stat::make('Reschedule Requests', Appointment::where('change_request_status', 'pending_reschedule')->count())
                ->icon('heroicon-o-arrow-path')
                ->color('info'),
        ];
    }
}
```

---

## 5.5 — Files to Create/Modify

| File | Action |
|------|--------|
| `app/Filament/Admin/Resources/Appointments/AppointmentResource.php` | **Modify** - Add columns, filters, actions |
| `app/Filament/Admin/Resources/Appointments/Tables/AppointmentTable.php` | **Create or Modify** - Table definition |
| `app/Filament/Admin/Resources/Appointments/Actions/` | **Create** - Confirm, Reject, Approve/Reject change requests |
| `app/Filament/Admin/Resources/Appointments/Pages/ViewAppointment.php` | **Modify** - Enhanced view with change request info |
| `app/Filament/Admin/Widgets/PendingAppointmentsWidget.php` | **Create** - Dashboard stats widget |
| `app/Filament/Admin/Widgets/AppointmentCalendarWidget.php` | **Modify** (if exists) - Ensure calendar reflects all statuses |
