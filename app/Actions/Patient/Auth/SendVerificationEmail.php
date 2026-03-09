<?php

namespace App\Actions\Patient\Auth;

use App\Mail\Patient\VerifyEmailMail;
use App\Models\Patient;
use Illuminate\Support\Facades\Mail;

class SendVerificationEmail
{
    public function handle(Patient $patient): void
    {
        $token = $patient->generateVerificationToken();

        $verificationUrl = config('app.frontend_url', config('app.url')) . '/verify-email?' . http_build_query([
            'token' => $token,
            'email' => $patient->email,
        ]);

        Mail::to($patient->email)->send(new VerifyEmailMail($patient, $verificationUrl));
    }
}
