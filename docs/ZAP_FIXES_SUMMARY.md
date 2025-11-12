# Zap Integration Fixes - Schedule Display and API Issues

## Summary

Fixed critical issues with how schedules are displayed and how availability is calculated. The root cause was misunderstanding of how Zap stores data:

-   **Times are stored in separate `schedule_periods` table**, NOT in `frequency_config`
-   **`frequency_config` only stores recurrence data** (e.g., days of week for weekly schedules)
-   Edit forms were trying to read from `frequency_config` instead of periods

---

## Issues Fixed

### 1. **Hours Not Displayed in Schedule Table**

**Problem:**

-   Table column was looking for times in `frequency_config['start_time']` and `frequency_config['end_time']`
-   Zap doesn't store times in frequency_config - it stores them in `schedule_periods` table

**Solution:**

-   Updated `SchedulesTable` to fetch times from `$record->periods()->get()`
-   Get times from first period: `$period->start_time` and `$period->end_time`

**File:** `/app/Filament/Admin/Resources/Doctors/Tables/SchedulesTable.php`

```php
// Before
$startTime = $config['start_time'] ?? null;
$endTime = $config['end_time'] ?? null;

// After
$periods = $record->periods()->get();
$period = $periods->first();
$startTime = $period->start_time ?? null;
$endTime = $period->end_time ?? null;
```

---

### 2. **Edit Availability - Days Not Checked, Times Not Set**

**Problem:**

-   `EditAvailabilityRuleSchema` was trying to get times from `frequency_config['start_time']` (doesn't exist)
-   Days were extracted from wrong format in `frequency_config['days_of_week']` (doesn't exist)
-   Zap stores days as string array: `['monday', 'tuesday', ...]` but checkbox list needs numeric values

**Solution:**

-   Get times from periods table (first period)
-   Convert day names from `frequency_config['days']` to numeric values for checkbox list
-   Proper day name → numeric mapping:
    -   'sunday' → 0, 'monday' → 1, 'tuesday' → 2, etc.

**File:** `/app/Filament/Admin/Resources/Doctors/Schemas/EditAvailabilityRuleSchema.php`

```php
// Get times from periods
$periods = $record->periods()->get();
$firstPeriod = $periods->first();
$startTime = $firstPeriod?->start_time ?? app(PlatformSettings::class)->start_time;
$endTime = $firstPeriod?->end_time ?? app(PlatformSettings::class)->end_time;

// Convert day names to numeric values
$daysOfWeekValues = $record->frequency_config['days'] ?? [];
$dayMap = ['sunday' => 0, 'monday' => 1, 'tuesday' => 2, ...];
$daysOfWeekNumeric = [];
foreach ($daysOfWeekValues as $day) {
    if (isset($dayMap[$day])) {
        $daysOfWeekNumeric[] = $dayMap[$day];
    }
}
```

---

### 3. **Edit Block Time - Times Reset, Toggle Always False**

**Problem:**

-   Looking for times in `frequency_config['start_time']` and `frequency_config['end_time']` (doesn't exist)
-   `has_time_restriction` toggle was detected from frequency_config (always false)
-   When editing, times would reset because they weren't being read correctly

**Solution:**

-   Get times from periods table (first period)
-   Detect time restriction by checking if period is NOT the full-day block (00:00-23:59)
-   If period is 00:00-23:59, it's a full-day block (no time restriction)
-   If period is any other time, it has time restriction

**File:** `/app/Filament/Admin/Resources/Doctors/Schemas/EditBlockTimeSchema.php`

```php
// Get times from periods
$periods = $record->periods()->get();
$firstPeriod = $periods->first();

// Detect time restriction
$hasTimeRestriction = false;
if ($firstPeriod) {
    // If NOT 00:00-23:59, then it has time restriction
    if (!($firstPeriod->start_time === '00:00' && $firstPeriod->end_time === '23:59')) {
        $hasTimeRestriction = true;
        $blockStartTime = $firstPeriod->start_time;
        $blockEndTime = $firstPeriod->end_time;
    }
}
```

---

### 4. **Availability API Returning True for Everyone**

**Problem:**

-   API was returning next available slot for every doctor regardless of whether they have availability schedules
-   No check for whether doctor has any availability rules configured
-   API should return `null` if no availability exists

**Solution:**

-   Added check: `$doctor->availabilitySchedules()->active()->exists()`
-   Return `null` for `next_available_slot` if no availability found
-   Add message explaining why no slot is available

**File:** `/app/Http/Controllers/Api/V1/DoctorAvailabilityController.php`

```php
// Check if doctor has any active availability schedules
$hasAvailability = $doctor->availabilitySchedules()
    ->active()
    ->exists();

if (!$hasAvailability) {
    return $this->sendResponse([
        'next_available_slot' => null,
        'message' => 'Doctor has no availability scheduled',
    ]);
}
```

---

### 5. **AddAvailabilityRule - Clean Up Metadata**

**Problem:**

-   Action was receiving all form data in `$metadata` array
-   Only name and description were needed
-   Extra fields could confuse Zap or cause issues

**Solution:**

-   Only use `$metadata['name']` and `$metadata['description']` if provided
-   Use Zap's fluent API properly: `->description($desc)` and `->active()`
-   Don't pass unnecessary metadata to Zap

**File:** `/app/Actions/Doctor/AddAvailabilityRule.php`

```php
$schedule = Zap::for($doctor)
    ->named($metadata['name'] ?? 'Availability Rule');

if (!empty($metadata['description'])) {
    $schedule->description($metadata['description']);
}

// ... set type, dates, periods, recurrence ...

if (isset($metadata['is_active'])) {
    if ($metadata['is_active']) {
        $schedule->active();
    }
} else {
    $schedule->active(); // Default to active
}

return $schedule->save();
```

---

## Zap Data Structure Reference

### Schedule Model

-   `name`: Schedule name
-   `description`: Optional description
-   `schedule_type`: 'availability', 'blocked', 'appointment', 'custom'
-   `start_date`: When schedule starts (Y-m-d)
-   `end_date`: When schedule ends (Y-m-d, nullable for ongoing)
-   `is_recurring`: boolean (true if frequency is set)
-   `frequency`: 'daily', 'weekly', 'monthly', or null
-   `frequency_config`: array with recurrence details (e.g., {'days': ['monday', 'tuesday', ...]})
-   `is_active`: boolean (schedule is active)

### SchedulePeriod Model (separate table)

-   `schedule_id`: FK to Schedule
-   `start_time`: Time period starts (H:i format, e.g., "09:00")
-   `end_time`: Time period ends (H:i format, e.g., "17:00")
-   `date`: Date for non-recurring (Y-m-d format)

### Key Insight

**Zap separates schedule metadata from schedule periods:**

-   Store **what happens** in `frequency_config` (e.g., which days repeat)
-   Store **when it happens** in `periods` table (e.g., 09:00-17:00)
-   For availability: one period (09:00-17:00) repeated weekly on Mon-Fri
-   For block times: one period (00:00-23:59 or specific hours) on date range

---

## How Zap Methods Work

### Creating an Availability Rule

```php
Zap::for($doctor)
    ->named('Regular Hours')
    ->description('Standard working hours')
    ->availability()
    ->from('2025-01-01')
    ->to('2025-12-31')                    // nullable for ongoing
    ->addPeriod('09:00', '17:00')         // STORES IN periods TABLE
    ->weekly(['monday', 'tuesday', ...])  // STORES IN frequency_config['days']
    ->active()
    ->save()
```

Result:

-   Schedule with `is_recurring=true`, `frequency='weekly'`, `frequency_config={'days': [...]}`
-   One period: 09:00-17:00
-   When checking availability on "2025-01-15" (Monday), Zap checks:
    -   Is this date within 2025-01-01 to 2025-12-31? ✓
    -   Is Monday in frequency_config['days']? ✓
    -   Does period 09:00-17:00 overlap with requested time? Check overlap logic

### Creating a Block Time

```php
Zap::for($doctor)
    ->named('Holiday')
    ->description('Company holiday')
    ->blocked()
    ->from('2025-01-01')
    ->to('2025-01-05')
    ->addPeriod('00:00', '23:59')  // FULL DAY BLOCK
    // OR
    ->addPeriod('14:00', '15:00')  // PARTIAL BLOCK
    ->save()
```

Result:

-   Schedule with `is_recurring=false` (no frequency set)
-   One period: 00:00-23:59 or specific hours
-   When checking availability, if period exists on date, time is unavailable

---

## Testing Checklist

After deploying these fixes:

-   [ ] Create availability rule, verify times display in table
-   [ ] Edit availability rule, verify:
    -   [ ] Days are checked
    -   [ ] Times are populated
    -   [ ] Times don't reset after save
-   [ ] Create block time (no time restriction), verify:
    -   [ ] Table shows full time range (00:00 → 23:59)
    -   [ ] Edit shows toggle OFF
-   [ ] Create block time (with time restriction), verify:
    -   [ ] Table shows specific time range (e.g., 14:00 → 15:00)
    -   [ ] Edit shows toggle ON with times
    -   [ ] Times don't reset after save
-   [ ] API availability endpoint:
    -   [ ] Returns `null` for doctor with no availability
    -   [ ] Returns next slot for doctor with availability
    -   [ ] Returns `null` if no slots available (all blocked)
    -   [ ] Respects blocked periods (skips blocked dates)

---

## Files Modified

1. `app/Filament/Admin/Resources/Doctors/Tables/SchedulesTable.php` - Read times from periods
2. `app/Filament/Admin/Resources/Doctors/Schemas/EditAvailabilityRuleSchema.php` - Read days and times from periods
3. `app/Filament/Admin/Resources/Doctors/Schemas/EditBlockTimeSchema.php` - Read times from periods, detect time restriction
4. `app/Actions/Doctor/AddAvailabilityRule.php` - Clean up metadata handling
5. `app/Http/Controllers/Api/V1/DoctorAvailabilityController.php` - Check for availability before returning slot

---

## Backwards Compatibility

✅ **Fully backwards compatible**

-   No breaking changes to API contracts
-   Existing schedules continue to work
-   Code correctly handles null/missing periods
-   Default values provided when data missing
