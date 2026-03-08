# Step 8: Event & Training Model Updates

Based on the requirements in `docs/new/events.txt` and `docs/new/training(learning).txt`, the existing Event and Training models need updates to their fields, API responses, and Filament resources.

---

## 8.1 — Event Model Updates

### Current State
```
Event: title (translatable), description (translatable), date, is_archived, location (translatable)
Media: gallery collection defined BUT HasMedia/InteractsWithMedia NOT implemented (broken)
```

### Required State (from docs/new/events.txt)

**API response fields needed:**
- `event_id` → exists as `id`
- `event_name` → exists as `title`
- `time` → **MISSING** — add `time` column
- `date` → exists
- `location` → exists
- `speakers` → **MISSING** — add `speakers` column (translatable JSON array)
- `about_event` → **MISSING** — add or alias to `description` (translatable)
- `pictures` → exists as `gallery` media collection but **broken** (HasMedia not implemented)
- `what_to_expect` → **MISSING** — add `what_to_expect` column (translatable)

**Categorization needed:**
- `archive events` → `is_archived = true` OR `date < today`
- `happening events` → `date = today`
- `future events` → `date > today`

### Changes to Event Model

**File:** `app/Models/Event.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;
use Spatie\MediaLibrary\HasMedia;              // ADD
use Spatie\MediaLibrary\InteractsWithMedia;    // ADD
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Event extends Model implements HasMedia    // ADD HasMedia
{
    use HasFactory, HasTranslations, InteractsWithMedia, SoftDeletes;  // ADD InteractsWithMedia

    protected $fillable = [
        'title', 'description', 'date', 'time', 'is_archived',
        'location', 'speakers', 'about_event', 'what_to_expect',
    ];

    public array $translatable = [
        'title', 'description', 'location',
        'speakers', 'about_event', 'what_to_expect',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'time' => 'datetime:H:i',
            'is_archived' => 'boolean',
        ];
    }

    // Event status: archive | happening | future
    public function getStatusAttribute(): string
    {
        if ($this->is_archived) return 'archive';
        if ($this->date->isToday()) return 'happening';
        if ($this->date->isFuture()) return 'future';
        return 'archive';
    }

    // Scopes for filtering
    public function scopeArchived($query) { return $query->where(fn($q) => $q->where('is_archived', true)->orWhere('date', '<', today())); }
    public function scopeHappening($query) { return $query->where('is_archived', false)->whereDate('date', today()); }
    public function scopeFuture($query) { return $query->where('is_archived', false)->where('date', '>', today()); }

    // existing media collections (now properly working)
    public function registerMediaCollections(): void { ... } // already defined
    public function registerMediaConversions(?Media $media = null): void { ... } // already defined
}
```

### Update Event API Response

**File:** `app/Actions/Event/ListEvents.php` and `app/Actions/Event/ShowEvent.php`

Update the response to include:

```php
// List response per event:
[
    'id' => $event->id,
    'name' => $event->title,         // use title as event name
    'date' => $event->date->format('Y-m-d'),
    'time' => $event->time?->format('H:i'),
    'location' => $event->location,
    'speakers' => $event->speakers,   // translatable, returns current locale
    'about_event' => $event->about_event ?? $event->description,
    'what_to_expect' => $event->what_to_expect,
    'pictures' => $event->getMedia('gallery')->map(fn($m) => [
        'url' => $m->getUrl(),
        'thumb' => $m->getUrl('thumb'),
    ]),
    'status' => $event->status,       // 'archive', 'happening', 'future'
]
```

### Update List Events Endpoint

Add `type` query parameter to filter by status:

```
GET /api/v1/events?type=archive
GET /api/v1/events?type=happening
GET /api/v1/events?type=future
GET /api/v1/events                    (all events)
```

### Update Filament EventResource

Add form fields for the new columns:
- `time` → TimePicker
- `speakers` → Translatable Textarea or Repeater (for multiple speakers)
- `about_event` → Translatable RichEditor
- `what_to_expect` → Translatable RichEditor
- Ensure `SpatieMediaLibraryFileUpload` for gallery works properly now that HasMedia is implemented

---

## 8.2 — Training Model Updates

### Current State
```
Training: title (translatable), description (translatable), trainer_name, duration, documents, video_url
Media: image (single), documents (multiple)
```

### Required State (from docs/new/training(learning).txt)

**API response fields needed:**
- `id` → exists
- `name` → exists as `title`
- `desc` → exists as `description`
- `images` → **NEEDS UPDATE** — currently only single `image`; need multiple `images` collection
- `reviews` → **MISSING** — new polymorphic relation
- `price` → **MISSING** — add column

### Changes to Training Model

**File:** `app/Models/Training.php`

Add to `$fillable`: `'price'`

Add to casts: `'price' => 'integer'`

Add new media collection:
```php
public function registerMediaCollections(): void
{
    $this->addMediaCollection('image')
        ->singleFile()
        ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/jpg'])
        ->useDisk('public');

    // NEW: Multiple images collection
    $this->addMediaCollection('images')
        ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/jpg'])
        ->useDisk('public');

    $this->addMediaCollection('documents')
        ->acceptsMimeTypes([...])
        ->useDisk('public');
}
```

Add reviews relation:
```php
public function reviews(): MorphMany
{
    return $this->morphMany(\App\Models\Review::class, 'reviewable');
}

public function approvedReviews(): MorphMany
{
    return $this->reviews()->where('is_approved', true);
}

public function getAverageRatingAttribute(): ?float
{
    return $this->approvedReviews()->avg('rating');
}
```

### Update Training API Response

```php
// List response per training:
[
    'id' => $training->id,
    'name' => $training->title,
    'description' => $training->description,
    'price' => $training->price,
    'images' => $training->getMedia('images')->map(fn($m) => [
        'url' => $m->getUrl(),
        'thumb' => $m->getUrl('thumb'),
    ]),
    'main_image' => $training->getFirstMediaUrl('image'),
    'average_rating' => $training->average_rating,
    'reviews_count' => $training->approved_reviews_count,
]

// Show response adds:
[
    ...,
    'trainer_name' => $training->trainer_name,
    'duration' => $training->duration,
    'video_url' => $training->video_url,
    'reviews' => $training->approvedReviews->map(fn($r) => [
        'id' => $r->id,
        'reviewer_name' => $r->reviewer_name ?? $r->patient?->full_name,
        'content' => $r->content,
        'rating' => $r->rating,
        'created_at' => $r->created_at->toDateTimeString(),
    ]),
]
```

### Training Reviews API Endpoint (optional)

Add endpoint for patients to submit reviews:

```
POST /api/v1/trainings/{training}/reviews   (auth required)
```

```json
{
  "content": "Great training, very informative!",
  "rating": 5
}
```

### Update Filament TrainingResource

- Add `price` field (TextInput, numeric)
- Add `SpatieMediaLibraryFileUpload` for `images` collection (multiple)
- Add relation manager for reviews (approve/reject reviews)

---

## 8.3 — Review Model

**File:** `app/Models/Review.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Review extends Model
{
    protected $fillable = [
        'reviewable_type', 'reviewable_id', 'patient_id',
        'reviewer_name', 'content', 'rating', 'is_approved',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'is_approved' => 'boolean',
        ];
    }

    public function reviewable(): MorphTo
    {
        return $this->morphTo();
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}
```

---

## 8.4 — Summary of Changes

### Migrations
1. Update events table (time, speakers, about_event, what_to_expect) — from Step 2
2. Update trainings table (price) — from Step 2
3. Create reviews table — from Step 2

### Model Changes
| Model | Changes |
|-------|---------|
| `Event` | Implement HasMedia, add InteractsWithMedia, add new fields/casts/translatable, add status accessor and scopes |
| `Training` | Add price, add images collection, add reviews relation, add average_rating accessor |
| `Review` | New model (polymorphic) |

### API Changes
| Endpoint | Changes |
|----------|---------|
| `GET /events` | Add `type` filter param, return new fields |
| `GET /events/{id}` | Return new fields |
| `GET /trainings` | Return price, images, rating |
| `GET /trainings/{id}` | Return reviews, full images |
| `POST /trainings/{id}/reviews` | New endpoint (auth required) |

### Filament Changes
| Resource | Changes |
|----------|---------|
| `EventResource` | Add form fields for new columns, fix media upload |
| `TrainingResource` | Add price field, images upload, reviews relation manager |
| New: `ReviewResource` or RelationManager | Manage review approvals |

### Actions to Create/Modify
| Action | File |
|--------|------|
| `ListEvents` | **Modify** - add type filter, return new fields |
| `ShowEvent` | **Modify** - return new fields |
| `ListTrainings` | **Modify** - return price, images, rating |
| `ShowTraining` | **Modify** - return reviews |
| `SubmitTrainingReview` | **Create** - `app/Actions/Training/SubmitTrainingReview.php` |
