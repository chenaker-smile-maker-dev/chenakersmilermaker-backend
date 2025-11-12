# Data Flow: Schedule Creation & Editing

## Create Availability Rule Flow

```
SchedulesTable (Filament Resource)
    ↓
HeaderActions::addAvailabilityRuleAction()
    ↓
CreateAvailabilityRuleSchema (Form Fields)
    ├─ name
    ├─ days_of_week (array: 0-6)
    ├─ start_hour (H:i)
    ├─ end_hour (H:i)
    ├─ effective_from (date, min: today)
    ├─ effective_to (date, min: today, nullable)
    ├─ description (nullable)
    └─ is_active (default: true)
    ↓
Form Action Closure (in HeaderActions)
    ↓
AddAvailabilityRule Action
    Parameters:
    - $doctor (Doctor model)
    - $data['days_of_week']
    - $data['start_hour']
    - $data['end_hour']
    - $data['effective_from']
    - $data['effective_to'] ?? null
    - $data (full metadata)
    ↓
Zap Facade: Zap::for($doctor)
    ->named()
    ->availability()
    ->from($effectiveFrom)
    ->to($effectiveTo)
    ->addPeriod($startHour, $endHour)
    ->weekly($dayNames)
    ->save()
    ↓
Schedule Model Created with Auto-Generated Periods
    ↓
Notification: "Availability Rule Added"
```

---

## Create Block Time Flow

```
SchedulesTable (Filament Resource)
    ↓
HeaderActions::addBlockTimeAction()
    ↓
CreateBlockTimeSchema (Form Fields)
    ├─ reason (required)
    ├─ start_date (date, min: today)
    ├─ end_date (date, min: today)
    ├─ has_time_restriction (toggle, default: false)
    ├─ block_start_time (H:i, visible if has_time_restriction)
    ├─ block_end_time (H:i, visible if has_time_restriction)
    ├─ description (nullable)
    └─ is_active (default: true)
    ↓
Form Action Closure (in HeaderActions)
    Extracts:
    - $data['reason']
    - $data['start_date']
    - $data['end_date']
    - $data['description'] ?? null
    - $data['has_time_restriction'] ? $data['block_start_time'] : null
    - $data['has_time_restriction'] ? $data['block_end_time'] : null
    ↓
AddBlockTime Action
    Parameters:
    - $doctor (Doctor model)
    - $reason
    - $fromDate (start_date)
    - $toDate (end_date)
    - $description
    - $blockStartTime (or null)
    - $blockEndTime (or null)
    - $metadata
    ↓
Zap Facade: Zap::for($doctor)
    ->named($reason)
    ->blocked()
    ->from($fromDate)
    ->to($toDate)
    ->addPeriod($blockStartTime, $blockEndTime) [or 00:00-23:59]
    ->save()
    ↓
Schedule Model Created with Blocked Periods
    ↓
Notification: "Block Time Added"
```

---

## Edit Schedule Flow

```
SchedulesTable (Filament Resource)
    Record selected
    ↓
RecordActions::editAction()
    ↓
Check: schedule_type === 'blocked' ?
    ├─ Yes → EditBlockTimeSchema
    └─ No → EditAvailabilityRuleSchema
    ↓
Form Pre-filled with Record Data
    (EditBlockTimeSchema)
    ├─ reason (from record.name)
    ├─ start_date (from record.start_date)
    ├─ end_date (from record.end_date)
    ├─ has_time_restriction (inferred from frequency_config)
    ├─ block_start_time (from frequency_config['start_time'])
    └─ block_end_time (from frequency_config['end_time'])

    (EditAvailabilityRuleSchema)
    ├─ name (from record.name)
    ├─ days_of_week (from frequency_config['days_of_week'])
    ├─ start_hour (from frequency_config['start_time'])
    ├─ end_hour (from frequency_config['end_time'])
    ├─ effective_from (from record.start_date)
    ├─ effective_to (from record.end_date)
    └─ description (from record.description)
    ↓
Form Action Closure (in RecordActions)
    ↓
UpdateSchedule Action
    - Receives $record (existing schedule)
    - Receives $data (updated form data)
    - Maps field names:
      * start_date = $data['effective_from'] ?? $data['start_date']
      * end_date = $data['effective_to'] ?? $data['end_date']
    ↓
Delete Old Schedule
    ↓
Create New Schedule via AddBlockTime or AddAvailabilityRule
    (Same flow as create)
    ↓
Notification: "Schedule Updated"
```

---

## Delete Schedule Flow

```
SchedulesTable (Filament Resource)
    Record selected
    ↓
RecordActions::deleteAction()
    ↓
Requires Confirmation
    ↓
DeleteSchedule Action
    - Receives $record (schedule to delete)
    - Deletes schedule and all related periods
    ↓
Notification: "Schedule Deleted"
```

---

## Field Name Mapping

### For Block Time Creation/Editing

| Form Field             | Action Parameter                  |
| ---------------------- | --------------------------------- |
| `reason`               | `$reason`                         |
| `start_date`           | `$fromDate`                       |
| `end_date`             | `$toDate`                         |
| `description`          | `$description`                    |
| `has_time_restriction` | Condition (truthiness)            |
| `block_start_time`     | `$blockStartTime` (if restricted) |
| `block_end_time`       | `$blockEndTime` (if restricted)   |
| `is_active`            | `$metadata['is_active']`          |

### For Availability Rule Creation/Editing

| Form Field       | Action Parameter           |
| ---------------- | -------------------------- |
| `name`           | `$metadata['name']`        |
| `days_of_week`   | `$daysOfWeek` (array)      |
| `start_hour`     | `$startHour`               |
| `end_hour`       | `$endHour`                 |
| `effective_from` | `$effectiveFrom`           |
| `effective_to`   | `$effectiveTo`             |
| `description`    | `$metadata['description']` |
| `is_active`      | `$metadata['is_active']`   |

---

## Validation Rules

### Create/Edit Block Time

-   ✅ `start_date` must be >= today
-   ✅ `end_date` must be >= today
-   ✅ `reason` is required
-   ✅ If `has_time_restriction` is true:
    -   `block_start_time` is required
    -   `block_end_time` is required
    -   Should validate: start_time < end_time
-   ✅ Zap Facade validates: from_date must be in future

### Create/Edit Availability Rule

-   ✅ `name` is required
-   ✅ `days_of_week` is required (must select at least one)
-   ✅ `effective_from` must be >= today
-   ✅ `effective_to` must be >= today (if provided)
-   ✅ `start_hour` < `end_hour` (UI doesn't enforce, but important)
-   ✅ Zap Facade validates: from_date must be in future

---

## Error Handling

### In HeaderActions & RecordActions

```php
try {
    // Execute action
} catch (\Exception $e) {
    Notification::make()
        ->danger()
        ->title('Error')
        ->body('Failed to [create/update/delete] schedule: ' . $e->getMessage())
        ->send();
}
```

Common errors:

-   `Doctor not found` → No doctor record selected
-   `cannot be created in the past` → Zap validation (Zap::save() validation)
-   Database constraints violated → Unexpected state
-   Action parameter missing → Form validation failed

---

## Success Path

1. ✅ Form validates all fields (required, type, min date)
2. ✅ Form closes and shows loading state
3. ✅ Form action extracts data and validates field names
4. ✅ Action class called with correct parameters
5. ✅ Zap Facade creates schedule with auto-generated periods
6. ✅ Success notification displayed
7. ✅ Table refreshed via `$livewire->dispatch('refresh')`
8. ✅ New/updated schedule visible in table
