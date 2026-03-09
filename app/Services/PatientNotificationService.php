<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\PatientNotification;

class PatientNotificationService
{
    /**
     * Send a notification to a patient.
     */
    public static function send(
        Patient $patient,
        string $type,
        array $title,
        array $body,
        array $data = [],
        ?string $actionUrl = null,
    ): PatientNotification {
        return PatientNotification::create([
            'patient_id' => $patient->id,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'data' => $data,
            'action_url' => $actionUrl,
        ]);
    }
}
