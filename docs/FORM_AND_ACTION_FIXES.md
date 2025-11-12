# Form and Action Fixes - Schedule Management

## Summary

Fixed multiple inconsistencies between form schemas and action classes for creating and editing availability rules and block times. All forms now use consistent field names and properly validate against past dates.

---

## Issues Fixed

### 1. **Block Time Form: Inconsistent Field Names Between Create and Edit**

**Problem:**

-   `CreateBlockTimeSchema` used toggle field name: `block_specific_hours`
-   `EditBlockTimeSchema` used toggle field name: `has_time_restriction`
-   Different date field names: `from_date`/`to_date` vs `start_date`/`end_date`
-   Field names didn't match action parameter expectations

**Solution:**

-   Standardized `CreateBlockTimeSchema` to use `start_date` and `end_date` (matching edit form and database schema)
-   Standardized both create and edit to use `has_time_restriction` toggle field name
-   Changed `reason` field name in edit form to match `reason` parameter in `AddBlockTime` action

**Files Modified:**

-   `/app/Filament/Admin/Resources/Doctors/Schemas/CreateBlockTimeSchema.php`
-   `/app/Filament/Admin/Resources/Doctors/Schemas/EditBlockTimeSchema.php`

---

### 2. **Availability Rule Form: Date Field Name Mismatch**

**Problem:**

-   `CreateAvailabilityRuleSchema` used field names: `effective_from`, `effective_to`
-   `EditAvailabilityRuleSchema` used field names: `start_date`, `end_date`
-   Form field names didn't match action parameter expectations

**Solution:**

-   Standardized `EditAvailabilityRuleSchema` to use `effective_from` and `effective_to` (matching create form and action parameters)

**Files Modified:**

-   `/app/Filament/Admin/Resources/Doctors/Schemas/EditAvailabilityRuleSchema.php`

---

### 3. **Missing Date Validation in Forms**

**Problem:**

-   Forms allowed selection of past dates
-   Zap Facade rejects schedules with past `from_date`, causing validation errors

**Solution:**

-   Added `->minDate(today())` to all DatePicker fields across all four form schemas
-   Prevents users from selecting dates in the past

**Files Modified:**

-   `/app/Filament/Admin/Resources/Doctors/Schemas/CreateBlockTimeSchema.php`
-   `/app/Filament/Admin/Resources/Doctors/Schemas/EditBlockTimeSchema.php`
-   `/app/Filament/Admin/Resources/Doctors/Schemas/CreateAvailabilityRuleSchema.php`
-   `/app/Filament/Admin/Resources/Doctors/Schemas/EditAvailabilityRuleSchema.php`

---

### 4. **HeaderActions Field Name Mismatches**

**Problem:**

-   `addBlockTimeAction()` was passing `from_date`, `to_date`, and `block_specific_hours` to the action
-   But form now uses `start_date`, `end_date`, and `has_time_restriction`

**Solution:**

-   Updated `addBlockTimeAction()` to extract correct field names from form data:
    -   Changed `$data['from_date']` → `$data['start_date']`
    -   Changed `$data['to_date']` → `$data['end_date']`
    -   Changed `$data['block_specific_hours']` → `$data['has_time_restriction']`

**Files Modified:**

-   `/app/Filament/Admin/Resources/Doctors/Actions/TableActions/HeaderActions.php`

---

### 5. **UpdateSchedule Action: Field Name Mapping**

**Problem:**

-   `UpdateSchedule` action was using old field names that didn't match form schemas
-   No handling for date field name differences between create and edit forms

**Solution:**

-   Added field name mapping to handle both old and new names:
    ```php
    $startDate = $data['effective_from'] ?? $data['start_date'] ?? null;
    $endDate = $data['effective_to'] ?? $data['end_date'] ?? null;
    ```
-   Updated block time field extraction to use `has_time_restriction` instead of `block_specific_hours`
-   Properly passes mapped data to `AddBlockTime` and `AddAvailabilityRule` actions

**Files Modified:**

-   `/app/Actions/Doctor/UpdateSchedule.php`

---

## Form Field Reference

### Block Time Forms

#### CreateBlockTimeSchema

| Field                  | Type       | Required      | Notes                                                       |
| ---------------------- | ---------- | ------------- | ----------------------------------------------------------- |
| `reason`               | TextInput  | Yes           | Reason for blocking (e.g., Holiday, Meeting)                |
| `start_date`           | DatePicker | Yes           | Block start date, min date: today                           |
| `end_date`             | DatePicker | Yes           | Block end date, min date: today                             |
| `has_time_restriction` | Toggle     | No            | Whether to block specific hours only                        |
| `block_start_time`     | TimePicker | Conditional\* | Block start time (visible if `has_time_restriction` = true) |
| `block_end_time`       | TimePicker | Conditional\* | Block end time (visible if `has_time_restriction` = true)   |
| `description`          | Textarea   | No            | Optional additional details                                 |
| `is_active`            | Toggle     | No            | Active status (default: true)                               |

\*Required when parent field is visible

#### EditBlockTimeSchema

Same as CreateBlockTimeSchema - now fully aligned

---

### Availability Rule Forms

#### CreateAvailabilityRuleSchema

| Field            | Type         | Required | Notes                                                |
| ---------------- | ------------ | -------- | ---------------------------------------------------- |
| `name`           | TextInput    | Yes      | Rule name (e.g., Regular Hours)                      |
| `days_of_week`   | CheckboxList | Yes      | Days of week (0=Sun, 1=Mon, etc.)                    |
| `start_hour`     | TimePicker   | Yes      | Start time of availability                           |
| `end_hour`       | TimePicker   | Yes      | End time of availability                             |
| `effective_from` | DatePicker   | Yes      | Effective start date, min date: today                |
| `effective_to`   | DatePicker   | No       | Effective end date (null = ongoing), min date: today |
| `description`    | Textarea     | No       | Optional additional details                          |
| `is_active`      | Toggle       | No       | Active status (default: true)                        |

#### EditAvailabilityRuleSchema

Same as CreateAvailabilityRuleSchema - now fully aligned

---

## Action Parameter Flow

### AddBlockTime Action

```php
AddBlockTime::__invoke(
    Doctor $doctor,
    string $reason,              // from form['reason']
    string $fromDate,            // from form['start_date']
    string $toDate,              // from form['end_date']
    ?string $description,        // from form['description']
    ?string $blockStartTime,     // from form['block_start_time'] (if has_time_restriction)
    ?string $blockEndTime,       // from form['block_end_time'] (if has_time_restriction)
    array $metadata              // entire form data
)
```

### AddAvailabilityRule Action

```php
AddAvailabilityRule::__invoke(
    Doctor $doctor,
    array $daysOfWeek,           // from form['days_of_week']
    string $startHour,           // from form['start_hour']
    string $endHour,             // from form['end_hour']
    string $effectiveFrom,       // from form['effective_from']
    ?string $effectiveTo,        // from form['effective_to']
    array $metadata              // entire form data
)
```

---

## Testing Checklist

-   [ ] Create availability rule with valid dates (today or future)
-   [ ] Edit existing availability rule
-   [ ] Try to create availability rule with past date (should be prevented by UI)
-   [ ] Create block time for entire day (toggle off)
-   [ ] Create block time for specific hours (toggle on, provide times)
-   [ ] Edit existing block time with time restriction
-   [ ] Edit existing block time without time restriction
-   [ ] Try to create block time with past date (should be prevented by UI)
-   [ ] Verify availability slots respect newly created rules
-   [ ] Verify availability slots show blocked periods as unavailable

---

## Database Schema Notes

The underlying database uses:

-   `start_date` and `end_date` columns (not `effective_from`/`effective_to`)
-   Form field names are different from database names for better UX
-   `EditBlockTimeSchema` automatically maps `start_date`/`end_date` to form defaults
-   `EditAvailabilityRuleSchema` automatically maps `effective_from`/`effective_to` from database fields

---

## Migration Path

If you need to migrate existing data:

1. Both field name mappings are backwards compatible (using nullish coalescing)
2. Old schedules can be updated without data migration
3. All new schedules created via forms will use Zap's fluent API for proper period generation
