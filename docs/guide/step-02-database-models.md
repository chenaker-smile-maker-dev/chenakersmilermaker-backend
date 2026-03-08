# Step 2: Database Migrations & Model Changes

This step covers all database schema changes needed before any feature work begins.

---

## 2.1 — Migration: Update Events Table

**File:** `database/migrations/YYYY_MM_DD_XXXXXX_update_events_table_add_missing_fields.php`

Add the fields required by `docs/new/events.txt`:

```php
Schema::table('events', function (Blueprint $table) {
    $table->time('time')->nullable()->after('date');           // event time (separate from date)
    $table->json('speakers')->nullable()->after('location');   // translatable array of speaker names/bios
    $table->json('about_event')->nullable()->after('speakers'); // translatable, replaces/supplements description
    $table->json('what_to_expect')->nullable()->after('about_event'); // translatable text
});
```

### Update Event Model (`app/Models/Event.php`)

1. **Implement `HasMedia` interface and use `InteractsWithMedia` trait** (currently missing — the model defines `registerMediaCollections` but doesn't implement the interface).
2. Add new fields to `$fillable`.
3. Add new translatable fields to `$translatable`.
4. Add `time` to casts.
5. Add computed attribute for event status: `archive`, `happening`, `future` (based on date vs now).

```php
class Event extends Model implements HasMedia
{
    use HasFactory, HasTranslations, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'title', 'description', 'date', 'time', 'is_archived',
        'location', 'speakers', 'about_event', 'what_to_expect',
    ];

    public array $translatable = [
        'title', 'description', 'location', 'speakers', 'about_event', 'what_to_expect',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'time' => 'datetime:H:i',
            'is_archived' => 'boolean',
            'speakers' => 'array',
        ];
    }

    // Computed: 'archive' | 'happening' | 'future'
    public function getStatusAttribute(): string
    {
        if ($this->is_archived) return 'archive';
        if ($this->date->isToday()) return 'happening';
        if ($this->date->isFuture()) return 'future';
        return 'archive'; // past events default to archive
    }
}
```

---

## 2.2 — Migration: Update Trainings Table

**File:** `database/migrations/YYYY_MM_DD_XXXXXX_update_trainings_table_add_missing_fields.php`

Add the fields required by `docs/new/training(learning).txt`:

```php
Schema::table('trainings', function (Blueprint $table) {
    $table->integer('price')->default(0)->after('duration');   // price in smallest currency unit
});
```

### Update Training Model (`app/Models/Training.php`)

1. Add `price` to `$fillable`.
2. Add `price` → `integer` cast.
3. Add a second media collection `images` (multiple images, separate from the single `image`).

**Note on reviews:** Reviews for trainings will be handled as a polymorphic `Review` model (see 2.5).

---

## 2.3 — Migration: Create Patient Notifications Table

**File:** `database/migrations/YYYY_MM_DD_XXXXXX_create_patient_notifications_table.php`

The Laravel `notifications` table already exists (migration `2025_09_16_161514`), but that's for the `User` model (admin). For patient notifications, we use a **dedicated table** with translatable content stored as JSON arrays:

```php
Schema::create('patient_notifications', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignId('patient_id')->constrained()->onDelete('cascade');
    $table->string('type');                    // e.g., 'appointment_confirmed', 'appointment_cancelled'
    $table->json('title');                     // translatable: {"en": "...", "ar": "...", "fr": "..."}
    $table->json('body');                      // translatable: {"en": "...", "ar": "...", "fr": "..."}
    $table->json('data')->nullable();          // extra payload (appointment_id, etc.)
    $table->string('action_url')->nullable();  // optional deep link / action URL
    $table->timestamp('read_at')->nullable();
    $table->timestamps();

    $table->index(['patient_id', 'read_at']);
    $table->index('type');
});
```

### Create PatientNotification Model

**File:** `app/Models/PatientNotification.php`

```php
class PatientNotification extends Model
{
    use HasUuids;

    protected $fillable = ['patient_id', 'type', 'title', 'body', 'data', 'action_url', 'read_at'];

    protected function casts(): array
    {
        return [
            'title' => 'array',  // {"en": "...", "ar": "...", "fr": "..."}
            'body' => 'array',
            'data' => 'array',
            'read_at' => 'datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    // Get title in current locale with fallback
    public function getLocalizedTitleAttribute(): string
    {
        $locale = app()->getLocale();
        return $this->title[$locale] ?? $this->title['en'] ?? '';
    }

    // Get body in current locale with fallback
    public function getLocalizedBodyAttribute(): string
    {
        $locale = app()->getLocale();
        return $this->body[$locale] ?? $this->body['en'] ?? '';
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function markAsRead(): void
    {
        $this->update(['read_at' => now()]);
    }
}
```

### Update Patient Model

Add the relation:
```php
public function notifications(): HasMany
{
    return $this->hasMany(PatientNotification::class)->orderByDesc('created_at');
}
```

---

## 2.4 — Migration: Create Urgent Bookings Table

**File:** `database/migrations/YYYY_MM_DD_XXXXXX_create_urgent_bookings_table.php`

This is a **completely separate** system from regular appointments:

```php
Schema::create('urgent_bookings', function (Blueprint $table) {
    $table->id();
    $table->foreignId('patient_id')->nullable()->constrained()->nullOnDelete(); // nullable for visitors
    $table->string('patient_name');           // name (for visitors who don't have account)
    $table->string('patient_phone');          // phone number
    $table->string('patient_email')->nullable();
    $table->text('reason');                   // why it's urgent
    $table->text('description')->nullable();  // additional details
    $table->string('status')->default('pending'); // pending, accepted, rejected, completed
    $table->text('admin_notes')->nullable();  // admin response/notes
    $table->foreignId('assigned_doctor_id')->nullable()->constrained('doctors')->nullOnDelete();
    $table->datetime('preferred_datetime')->nullable(); // when they'd like to come
    $table->datetime('scheduled_datetime')->nullable(); // admin-assigned actual time
    $table->json('metadata')->nullable();     // extra data
    $table->timestamps();
    $table->softDeletes();

    $table->index('status');
    $table->index('patient_phone');
});
```

### Create UrgentBooking Model

**File:** `app/Models/UrgentBooking.php`

```php
class UrgentBooking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'patient_id', 'patient_name', 'patient_phone', 'patient_email',
        'reason', 'description', 'status', 'admin_notes',
        'assigned_doctor_id', 'preferred_datetime', 'scheduled_datetime', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'preferred_datetime' => 'datetime',
            'scheduled_datetime' => 'datetime',
            'status' => UrgentBookingStatus::class,
            'metadata' => 'array',
        ];
    }

    public function patient(): BelongsTo { ... }
    public function assignedDoctor(): BelongsTo { ... }
}
```

### Create UrgentBookingStatus Enum

**File:** `app/Enums/UrgentBookingStatus.php`

```php
enum UrgentBookingStatus: string implements HasLabel, HasColor, HasIcon
{
    case PENDING = 'pending';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';
    case COMPLETED = 'completed';
}
```

---

## 2.5 — Migration: Create Reviews Table (for Trainings)

**File:** `database/migrations/YYYY_MM_DD_XXXXXX_create_reviews_table.php`

Polymorphic reviews (trainings can have reviews, extensible to other models later):

```php
Schema::create('reviews', function (Blueprint $table) {
    $table->id();
    $table->morphs('reviewable');             // reviewable_type, reviewable_id
    $table->foreignId('patient_id')->nullable()->constrained()->nullOnDelete();
    $table->string('reviewer_name')->nullable(); // for non-authenticated reviewers
    $table->text('content')->nullable();
    $table->unsignedTinyInteger('rating');    // 1-5
    $table->boolean('is_approved')->default(false);
    $table->timestamps();

    $table->index(['reviewable_type', 'reviewable_id']);
});
```

### Update Training Model

Add the relation:
```php
public function reviews(): MorphMany
{
    return $this->morphMany(Review::class, 'reviewable');
}

public function approvedReviews(): MorphMany
{
    return $this->reviews()->where('is_approved', true);
}
```

---

## 2.6 — Migration: Add Appointment Management Fields

**File:** `database/migrations/YYYY_MM_DD_XXXXXX_add_management_fields_to_appointments_table.php`

```php
Schema::table('appointments', function (Blueprint $table) {
    $table->text('admin_notes')->nullable()->after('metadata');
    $table->text('cancellation_reason')->nullable()->after('admin_notes');
    $table->text('reschedule_reason')->nullable()->after('cancellation_reason');
    $table->datetime('original_from')->nullable()->after('reschedule_reason'); // for reschedule tracking
    $table->datetime('original_to')->nullable()->after('original_from');
    $table->string('change_request_status')->nullable()->after('original_to'); // pending_cancellation, pending_reschedule
    $table->datetime('requested_new_from')->nullable()->after('change_request_status'); // patient's requested new time
    $table->datetime('requested_new_to')->nullable()->after('requested_new_from');
    $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete(); // admin who confirmed
    $table->timestamp('confirmed_at')->nullable();
});
```

### Update Appointment Model

Add new fields to `$fillable`, add casts, add relation:
```php
public function confirmedBy(): BelongsTo
{
    return $this->belongsTo(User::class, 'confirmed_by');
}
```

### Create AppointmentChangeRequestStatus Enum (or extend metadata)

Values: `pending_cancellation`, `pending_reschedule`, `approved`, `rejected`

---

## 2.7 — Migration: Add Email Verification to Patients

**File:** `database/migrations/YYYY_MM_DD_XXXXXX_add_email_verification_to_patients_table.php`

```php
Schema::table('patients', function (Blueprint $table) {
    // email_verified_at already exists in cast but check if column exists
    if (!Schema::hasColumn('patients', 'email_verified_at')) {
        $table->timestamp('email_verified_at')->nullable()->after('email');
    }
    $table->string('email_verification_token')->nullable()->after('email_verified_at');
    $table->timestamp('email_verification_sent_at')->nullable()->after('email_verification_token');
});
```

---

## Summary of All Migrations

| # | Migration | Type |
|---|-----------|------|
| 1 | Update events table (time, speakers, about_event, what_to_expect) | ALTER |
| 2 | Update trainings table (price) | ALTER |
| 3 | Create patient_notifications table | CREATE |
| 4 | Create urgent_bookings table | CREATE |
| 5 | Create reviews table (polymorphic) | CREATE |
| 6 | Add management fields to appointments table | ALTER |
| 7 | Add email verification fields to patients table | ALTER |

**Run order:** All can run in any order as they have no cross-dependencies.

---

## New Models Summary

| Model | File | Notes |
|-------|------|-------|
| `PatientNotification` | `app/Models/PatientNotification.php` | UUID primary key, translatable arrays |
| `UrgentBooking` | `app/Models/UrgentBooking.php` | Separate from Appointment |
| `UrgentBookingStatus` | `app/Enums/UrgentBookingStatus.php` | Enum with Filament contracts |
| `Review` | `app/Models/Review.php` | Polymorphic, used by Training (and extensible) |

## Updated Models Summary

| Model | Changes |
|-------|---------|
| `Event` | Implement HasMedia, add new fields, add status accessor |
| `Training` | Add price, add reviews relation, add images collection |
| `Patient` | Add notifications relation, add email verification fields |
| `Appointment` | Add management fields, add confirmedBy relation, add change request logic |
