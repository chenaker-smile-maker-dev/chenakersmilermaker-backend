<?php

namespace App\Actions\Patient\Auth;

use App\Enums\PatientNotificationType;
use App\Models\Patient;
use App\Services\PatientNotificationService;
use App\Services\PatientNotificationTemplates;

class VerifyPatientEmail
{
    public function handle(string $email, string $token): array
    {
        $patient = Patient::where('email', $email)
            ->where('email_verification_token', $token)
            ->first();

        if (!$patient) {
            return ['success' => false, 'message' => 'api.token_invalid'];
        }

        // Check token expiry (24 hours)
        if (
            $patient->email_verification_sent_at &&
            $patient->email_verification_sent_at->addHours(24)->isPast()
        ) {
            return ['success' => false, 'message' => 'api.token_expired'];
        }

        $patient->markEmailAsVerified();

        // Send welcome notification
        PatientNotificationService::send(
            $patient,
            PatientNotificationType::EMAIL_VERIFIED->value,
            PatientNotificationTemplates::emailVerified()['title'],
            PatientNotificationTemplates::emailVerified()['body'],
        );

        return ['success' => true, 'message' => 'api.email_verified'];
    }
}
