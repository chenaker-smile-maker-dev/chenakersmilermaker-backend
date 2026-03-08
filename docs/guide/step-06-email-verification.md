# Step 6: Patient Email Verification

Implement email verification for patients using custom Blade email templates.

---

## 6.1 — Update Patient Model

**File:** `app/Models/Patient.php`

The Patient model already has `email_verified_at` in its casts. Add:

1. **Do NOT implement `MustVerifyEmail`** (that's for the default Laravel flow which is tied to `User`). Instead, implement a custom verification flow.
2. Add helper methods:

```php
// Add to $fillable
'email_verified_at',
'email_verification_token',
'email_verification_sent_at',

// Helper methods
public function hasVerifiedEmail(): bool
{
    return $this->email_verified_at !== null;
}

public function markEmailAsVerified(): bool
{
    return $this->forceFill([
        'email_verified_at' => now(),
        'email_verification_token' => null,
    ])->save();
}

public function generateVerificationToken(): string
{
    $token = Str::random(64);
    $this->update([
        'email_verification_token' => $token,
        'email_verification_sent_at' => now(),
    ]);
    return $token;
}
```

---

## 6.2 — Custom Blade Email Templates

### Directory Structure

```
resources/views/emails/
├── patient/
│   ├── verify-email.blade.php
│   └── layouts/
│       └── base.blade.php
```

### Base Layout

**File:** `resources/views/emails/patient/layouts/base.blade.php`

```blade
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Chenaker Smile Maker</title>
    <style>
        /* Inline CSS for email compatibility */
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 8px; overflow: hidden; }
        .header { background-color: #25703e; padding: 20px; text-align: center; }
        .header img { max-width: 150px; }
        .header h1 { color: #ffffff; margin: 10px 0 0; font-size: 22px; }
        .content { padding: 30px; }
        .btn { display: inline-block; padding: 12px 30px; background-color: #25703e; color: #ffffff; text-decoration: none; border-radius: 5px; font-weight: bold; }
        .footer { padding: 20px; text-align: center; font-size: 12px; color: #888; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Chenaker Smile Maker</h1>
        </div>
        <div class="content">
            @yield('content')
        </div>
        <div class="footer">
            © {{ date('Y') }} Chenaker Smile Maker. All rights reserved.
        </div>
    </div>
</body>
</html>
```

### Verification Email Template

**File:** `resources/views/emails/patient/verify-email.blade.php`

```blade
@extends('emails.patient.layouts.base')

@section('title', __('Verify Your Email'))

@section('content')
    <h2>{{ __('Hello') }}, {{ $patient->first_name }}!</h2>

    <p>{{ __('Thank you for registering with Chenaker Smile Maker. Please verify your email address by clicking the button below.') }}</p>

    <p style="text-align: center; margin: 30px 0;">
        <a href="{{ $verificationUrl }}" class="btn">
            {{ __('Verify Email Address') }}
        </a>
    </p>

    <p>{{ __('If you did not create an account, no further action is required.') }}</p>

    <p style="font-size: 12px; color: #888;">
        {{ __('If the button does not work, copy and paste this URL into your browser:') }}<br>
        <a href="{{ $verificationUrl }}">{{ $verificationUrl }}</a>
    </p>
@endsection
```

---

## 6.3 — Mailable Class

**File:** `app/Mail/Patient/VerifyEmailMail.php`

```php
<?php

namespace App\Mail\Patient;

use App\Models\Patient;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VerifyEmailMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Patient $patient,
        public string $verificationUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Verify Your Email - Chenaker Smile Maker'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.patient.verify-email',
            with: [
                'patient' => $this->patient,
                'verificationUrl' => $this->verificationUrl,
            ],
        );
    }
}
```

---

## 6.4 — Verification Action

**File:** `app/Actions/Patient/Auth/SendVerificationEmail.php`

```php
class SendVerificationEmail
{
    public function handle(Patient $patient): void
    {
        $token = $patient->generateVerificationToken();

        // Build verification URL (frontend URL that calls the verify endpoint)
        $verificationUrl = config('app.frontend_url') . '/verify-email?' . http_build_query([
            'token' => $token,
            'email' => $patient->email,
        ]);

        Mail::to($patient->email)->send(new VerifyEmailMail($patient, $verificationUrl));
    }
}
```

**File:** `app/Actions/Patient/Auth/VerifyPatientEmail.php`

```php
class VerifyPatientEmail
{
    public function handle(string $email, string $token): array
    {
        $patient = Patient::where('email', $email)
            ->where('email_verification_token', $token)
            ->first();

        if (!$patient) {
            return ['success' => false, 'message' => 'Invalid verification token.'];
        }

        // Check token expiry (e.g., 24 hours)
        if ($patient->email_verification_sent_at &&
            $patient->email_verification_sent_at->addHours(24)->isPast()) {
            return ['success' => false, 'message' => 'Verification link has expired. Please request a new one.'];
        }

        $patient->markEmailAsVerified();

        // Send welcome notification
        PatientNotificationService::send(
            $patient,
            PatientNotificationType::EMAIL_VERIFIED->value,
            ...PatientNotificationTemplates::emailVerified(),
        );

        return ['success' => true, 'message' => 'Email verified successfully.'];
    }
}
```

---

## 6.5 — API Endpoints

Add to `routes/api/v1.php`:

```php
Route::prefix('patient/auth')->group(function () {
    // ... existing auth routes ...

    Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
    Route::post('/resend-verification', [AuthController::class, 'resendVerification'])
        ->middleware(['auth:sanctum', 'access']);
});
```

### POST `/api/v1/patient/auth/verify-email`

**No authentication required** (patient clicks link in email).

**Request Body:**
```json
{
  "email": "john@example.com",
  "token": "random64chartokenstring..."
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Email verified successfully."
}
```

**Errors:**
- 422: Invalid token or expired

### POST `/api/v1/patient/auth/resend-verification`

**Authentication required.**

**Response (200):**
```json
{
  "success": true,
  "message": "Verification email sent."
}
```

**Logic:**
1. Check if already verified → return message.
2. Rate limit: Don't send if last sent < 1 minute ago.
3. Generate new token and send email.

---

## 6.6 — Integration Points

### On Registration

In the `RegisterPatient` action (or the `register` method in `AuthController`), after creating the patient:

```php
// After patient is created and tokens generated
app(SendVerificationEmail::class)->handle($patient);
```

### Middleware for Verified Email

Create a middleware to optionally gate booking behind email verification:

**File:** `app/Http/Middleware/EnsurePatientEmailIsVerified.php`

```php
class EnsurePatientEmailIsVerified
{
    public function handle(Request $request, Closure $next)
    {
        $patient = $request->user();

        if ($patient instanceof Patient && !$patient->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' => 'Please verify your email address before proceeding.',
                'data' => ['email_verified' => false],
            ], 403);
        }

        return $next($request);
    }
}
```

Register in `bootstrap/app.php` or Kernel:
```php
'verified.patient' => EnsurePatientEmailIsVerified::class,
```

Apply to booking route:
```php
Route::prefix('booking')
    ->middleware(['auth:sanctum', 'access', 'verified.patient'])
    ->group(function () { ... });
```

---

## 6.7 — Config

Add to `config/app.php` or `.env`:

```env
FRONTEND_URL=https://your-frontend-domain.com
```

---

## 6.8 — Files to Create

| File | Description |
|------|-------------|
| `resources/views/emails/patient/layouts/base.blade.php` | Email base layout |
| `resources/views/emails/patient/verify-email.blade.php` | Verification email template |
| `app/Mail/Patient/VerifyEmailMail.php` | Mailable class |
| `app/Actions/Patient/Auth/SendVerificationEmail.php` | Send verification action |
| `app/Actions/Patient/Auth/VerifyPatientEmail.php` | Verify token action |
| `app/Http/Middleware/EnsurePatientEmailIsVerified.php` | Middleware |
| `database/migrations/..._add_email_verification_to_patients_table.php` | Migration (from Step 2) |
