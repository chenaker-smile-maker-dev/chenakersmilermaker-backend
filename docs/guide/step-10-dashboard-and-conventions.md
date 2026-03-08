# Step 10: Custom Dashboard, API Conventions & Filament Polish

This step enforces **critical cross-cutting conventions** across the entire codebase, and creates a proper admin dashboard.

---

## 10.1 — API Translatability Convention (MANDATORY)

**Every translatable field exposed via API MUST return all 3 locales as an object.**

The project already has `app/Utils/GetModelMultilangAttribute.php` which reads `config('default-local.available_locals')` → `['fr', 'ar', 'en']` and returns:

```json
{
  "name": {
    "ar": "طبيب أسنان",
    "fr": "Dentiste",
    "en": "Dentist"
  }
}
```

### Rules

1. **All API Resource classes** (`DoctorResource`, `ServiceResource`, `TestimonialResource`, etc.) MUST use `GetModelMultilangAttribute::get($this, 'field')` for every translatable field. **Fix TestimonialResource** — it currently returns `$this->name` (single locale only).

2. **All Action classes** that return models directly (like `ListEvents`, `ShowEvent`, `ListTrainings`, `ShowTraining`) MUST transform translatable fields using `GetModelMultilangAttribute` or `$model->getTranslations('field')`. Do NOT return raw Eloquent models — always map/transform.

3. **Error messages and validation messages** MUST be translatable. Use Laravel's `lang/` directory:
   - Add validation messages to `lang/ar/validation.php`, `lang/fr/validation.php`, `lang/en/validation.php`
   - Add custom API error messages to `lang/ar/api.php`, `lang/fr/api.php`, `lang/en/api.php`
   - Use `__('api.cancellation_submitted')` or `trans()` for all user-facing strings in API responses
   - The `BaseController::sendResponse()` and `sendError()` message parameters should always use `__()` translated strings

4. **Create `lang/ar/api.php`**, `lang/fr/api.php`, `lang/en/api.php` with all API response messages:
   ```php
   // lang/en/api.php
   return [
       'cancellation_submitted' => 'Cancellation request submitted successfully. Awaiting admin approval.',
       'reschedule_submitted' => 'Reschedule request submitted successfully. Awaiting admin approval.',
       'appointment_not_yours' => 'This appointment does not belong to you.',
       'invalid_status_for_cancellation' => 'Cannot cancel an appointment with status :status.',
       'pending_request_exists' => 'A pending change request already exists for this appointment.',
       'email_verification_sent' => 'Verification email sent successfully.',
       'email_already_verified' => 'Email is already verified.',
       'email_verified' => 'Email verified successfully.',
       'token_expired' => 'Verification token has expired.',
       'token_invalid' => 'Invalid verification token.',
       'urgent_booking_submitted' => 'Your urgent booking request has been submitted. We will contact you shortly.',
       'review_submitted' => 'Your review has been submitted and is awaiting approval.',
       'login_success' => 'Logged in successfully.',
       'logout_success' => 'Logged out successfully.',
       'register_success' => 'Account created successfully.',
       'profile_updated' => 'Profile updated successfully.',
       'password_updated' => 'Password updated successfully.',
       'email_not_verified' => 'Please verify your email address before proceeding.',
       'notification_marked_read' => 'Notification marked as read.',
       'all_notifications_marked_read' => 'All notifications marked as read.',
       // ... etc for every user-facing message
   ];
   ```

---

## 10.2 — API Media Convention (MANDATORY)

**Every image/media field returned via API MUST include both the original URL and ALL registered conversions.**

### Standard Media Response Format

```php
// For single media (e.g., doctor photo, service image):
'image' => $model->getFirstMedia('collection_name') ? [
    'original' => $model->getFirstMediaUrl('collection_name'),
    'thumb' => $model->getFirstMediaUrl('collection_name', 'thumb'),
    // include ALL conversions registered in registerMediaConversions()
] : null,

// For multiple media (e.g., event gallery, training images):
'images' => $model->getMedia('collection_name')->map(fn($media) => [
    'id' => $media->id,
    'original' => $media->getUrl(),
    'thumb' => $media->getUrl('thumb'),
    // include ALL conversions registered in registerMediaConversions()
]),
```

### Apply To These Resources

| Resource | Collection | Current | Required |
|----------|-----------|---------|----------|
| `DoctorResource` | `doctor_photo` | `image` + `thumb_image` as flat strings | `image: {original, thumb}` object |
| `ServiceResource` | `image` | `image` + `thumb_image` as flat strings | `image: {original, thumb}` object |
| `PatientResource` (API) | `patient_photo` | varies | `image: {original, thumb}` object |
| Event API response | `gallery` | step-08 shows correct format | Ensure `{id, original, thumb}` per item |
| Training API response | `image` + `images` | step-08 shows correct format | Ensure `{original, thumb}` for main, `{id, original, thumb}` per gallery item |
| `TestimonialResource` | none currently | N/A | N/A |

### Create a Reusable Helper

```php
// app/Utils/MediaHelper.php
class MediaHelper
{
    /**
     * Format a single media item for API response.
     */
    public static function single(Model $model, string $collection): ?array
    {
        $media = $model->getFirstMedia($collection);
        if (!$media) return null;

        $result = ['original' => $media->getUrl()];
        foreach ($media->getGeneratedConversions() as $conversion => $generated) {
            if ($generated) {
                $result[$conversion] = $media->getUrl($conversion);
            }
        }
        return $result;
    }

    /**
     * Format multiple media items for API response.
     */
    public static function collection(Model $model, string $collection): array
    {
        return $model->getMedia($collection)->map(function ($media) {
            $result = [
                'id' => $media->id,
                'original' => $media->getUrl(),
            ];
            foreach ($media->getGeneratedConversions() as $conversion => $generated) {
                if ($generated) {
                    $result[$conversion] = $media->getUrl($conversion);
                }
            }
            return $result;
        })->toArray();
    }
}
```

Usage:
```php
'image' => MediaHelper::single($this, 'doctor_photo'),
'gallery' => MediaHelper::collection($event, 'gallery'),
```

---

## 10.3 — Override Filament Dashboard Page

The current dashboard has only the `AppointmentCalendarWidget`. Override the dashboard with a custom page that has comprehensive widgets.

### Create Custom Dashboard Page

**File:** `app/Filament/Admin/Pages/Dashboard.php`

```php
<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    public function getWidgets(): array
    {
        return [
            \App\Filament\Admin\Widgets\StatsOverviewWidget::class,
            \App\Filament\Admin\Widgets\PendingActionsWidget::class,
            \App\Filament\Admin\Widgets\TodayAppointmentsWidget::class,
            \App\Filament\Admin\Widgets\UrgentBookingsWidget::class,
            \App\Filament\Admin\Widgets\AppointmentCalendarWidget::class,
            \App\Filament\Admin\Widgets\RecentActivityWidget::class,
        ];
    }

    public function getColumns(): int|string|array
    {
        return [
            'default' => 1,
            'md' => 2,
            'xl' => 3,
        ];
    }
}
```

### Update AdminPanelProvider

Replace `Dashboard::class` import with the custom one:
```php
use App\Filament\Admin\Pages\Dashboard;

// In panel():
->pages([Dashboard::class])
```

Remove widgets from panel provider (they're now in the Dashboard page):
```php
->widgets([
    // Empty — all widgets registered in custom Dashboard page
])
```

---

## 10.4 — Dashboard Widgets to Create

### 1. StatsOverviewWidget (Full Width)

**File:** `app/Filament/Admin/Widgets/StatsOverviewWidget.php`

```php
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getColumns(): int { return 4; }

    protected function getStats(): array
    {
        return [
            Stat::make('Total Patients', Patient::count())
                ->icon('heroicon-o-users')
                ->color('primary')
                ->description('Registered patients')
                ->descriptionIcon('heroicon-m-user-group'),

            Stat::make('Today\'s Appointments', Appointment::whereDate('from', today())->count())
                ->icon('heroicon-o-calendar')
                ->color('success')
                ->description(Appointment::whereDate('from', today())->where('status', 'confirmed')->count() . ' confirmed'),

            Stat::make('This Month', Appointment::whereMonth('from', now()->month)->whereYear('from', now()->year)->count())
                ->icon('heroicon-o-chart-bar')
                ->color('info')
                ->description('Appointments this month'),

            Stat::make('Pending Actions', 
                Appointment::where('status', 'pending')->count() + 
                Appointment::whereIn('change_request_status', ['pending_cancellation', 'pending_reschedule'])->count() +
                UrgentBooking::where('status', 'pending')->count()
            )
                ->icon('heroicon-o-exclamation-triangle')
                ->color('danger')
                ->description('Require your attention'),
        ];
    }
}
```

### 2. PendingActionsWidget (Detailed Breakdown)

**File:** `app/Filament/Admin/Widgets/PendingActionsWidget.php`

```php
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PendingActionsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getColumns(): int { return 3; }

    protected function getStats(): array
    {
        return [
            Stat::make('Pending Appointments', Appointment::where('status', 'pending')->count())
                ->icon('heroicon-o-clock')
                ->color('warning')
                ->url(AppointmentResource::getUrl('index', ['tableFilters[status][value]' => 'pending'])),

            Stat::make('Cancellation Requests', Appointment::where('change_request_status', 'pending_cancellation')->count())
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->url(AppointmentResource::getUrl('index', ['tableFilters[change_request][value]' => 'pending_cancellation'])),

            Stat::make('Reschedule Requests', Appointment::where('change_request_status', 'pending_reschedule')->count())
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->url(AppointmentResource::getUrl('index', ['tableFilters[change_request][value]' => 'pending_reschedule'])),
        ];
    }
}
```

### 3. TodayAppointmentsWidget (Table)

**File:** `app/Filament/Admin/Widgets/TodayAppointmentsWidget.php`

A table widget showing today's appointments with status, doctor, patient, and time:

```php
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;

class TodayAppointmentsWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = "Today's Appointments";

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->query(
                Appointment::query()
                    ->whereDate('from', today())
                    ->with(['doctor', 'patient', 'service'])
                    ->orderBy('from')
            )
            ->columns([
                Tables\Columns\TextColumn::make('from')->label('Time')->dateTime('H:i')->sortable(),
                Tables\Columns\TextColumn::make('patient.full_name')->label('Patient')->searchable(),
                Tables\Columns\TextColumn::make('doctor.display_name')->label('Doctor'),
                Tables\Columns\TextColumn::make('service.name')->label('Service'),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'confirmed',
                        'danger' => fn($state) => in_array($state, ['rejected', 'cancelled']),
                        'primary' => 'completed',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->url(fn(Appointment $record) => AppointmentResource::getUrl('view', ['record' => $record]))
                    ->icon('heroicon-o-eye'),
            ])
            ->emptyStateHeading('No appointments today')
            ->emptyStateIcon('heroicon-o-calendar')
            ->paginated(false);
    }
}
```

### 4. UrgentBookingsWidget (already in step-07, ensure it's created)

Shows pending urgent bookings count + latest entries. Already specified in step-07-urgent-booking.md section "Dashboard Widget".

### 5. RecentActivityWidget

**File:** `app/Filament/Admin/Widgets/RecentActivityWidget.php`

Shows latest system activity (new patients, new bookings, status changes):

```php
use Filament\Widgets\Widget;

class RecentActivityWidget extends Widget
{
    protected static ?int $sort = 6;
    protected static string $view = 'filament.admin.widgets.recent-activity';
    protected int | string | array $columnSpan = 1;

    public function getActivities(): Collection
    {
        // Combine recent events from multiple sources:
        $recentAppointments = Appointment::latest()->take(5)->get()->map(fn($a) => [
            'icon' => 'heroicon-o-calendar',
            'color' => 'primary',
            'title' => "New appointment booked",
            'description' => "{$a->patient->full_name} with {$a->doctor->display_name}",
            'time' => $a->created_at,
        ]);

        $recentPatients = Patient::latest()->take(3)->get()->map(fn($p) => [
            'icon' => 'heroicon-o-user-plus',
            'color' => 'success',
            'title' => "New patient registered",
            'description' => $p->full_name,
            'time' => $p->created_at,
        ]);

        $recentUrgent = UrgentBooking::latest()->take(3)->get()->map(fn($u) => [
            'icon' => 'heroicon-o-exclamation-triangle',
            'color' => 'danger',
            'title' => "Urgent booking received",
            'description' => $u->patient_name,
            'time' => $u->created_at,
        ]);

        return collect()
            ->merge($recentAppointments)
            ->merge($recentPatients)
            ->merge($recentUrgent)
            ->sortByDesc('time')
            ->take(10);
    }
}
```

Create a corresponding Blade view:
**File:** `resources/views/filament/admin/widgets/recent-activity.blade.php`

A simple timeline/list of recent activity items with icons, titles, descriptions, and relative timestamps.

### 6. AppointmentCalendarWidget (EXISTING — keep)

Already exists at `app/Filament/Admin/Widgets/AppointmentCalendarWidget.php`. Keep it but set `$sort = 5` and `$columnSpan = 'full'`.

---

## 10.5 — Filament Resource Polish

Ensure ALL Filament resources have proper CRUD actions working:

### Events Resource
- Verify form fields work for new columns (time, speakers, about_event, what_to_expect) after step-08 migration
- Ensure `SpatieMediaLibraryFileUpload` for gallery collection works after HasMedia fix
- Add translatable field support using Filament's `Translatable` plugin or manual tabs

### Trainings Resource
- Add `price` field to form
- Add `SpatieMediaLibraryFileUpload` for `images` collection (multiple)
- Add Reviews RelationManager for managing/approving reviews

### Testimonials Resource
- Ensure publish/unpublish toggle action works
- Verify all fields display correctly

### Doctors Resource
- Verify schedule management works (Zap integration)
- Ensure service assignment (many-to-many) works

### Patients Resource
- Add email verification status indicator
- Add notification count display
- Consider a "Send Verification Email" action button

---

## 10.6 — Files Summary

| File | Action |
|------|--------|
| `app/Filament/Admin/Pages/Dashboard.php` | **Create** — Custom dashboard page |
| `app/Filament/Admin/Widgets/StatsOverviewWidget.php` | **Create** — Main stats |
| `app/Filament/Admin/Widgets/PendingActionsWidget.php` | **Create** — Pending items breakdown |
| `app/Filament/Admin/Widgets/TodayAppointmentsWidget.php` | **Create** — Table of today's appointments |
| `app/Filament/Admin/Widgets/RecentActivityWidget.php` | **Create** — Activity feed |
| `resources/views/filament/admin/widgets/recent-activity.blade.php` | **Create** — Activity view |
| `app/Providers/Filament/AdminPanelProvider.php` | **Modify** — Use custom Dashboard, remove widget list |
| `app/Utils/MediaHelper.php` | **Create** — Reusable media formatter |
| `lang/en/api.php` | **Create** — English API messages |
| `lang/ar/api.php` | **Create** — Arabic API messages |
| `lang/fr/api.php` | **Create** — French API messages |
| All API Resources | **Modify** — Apply translation + media conventions |
| All Actions returning data | **Modify** — Apply translation + media conventions |
