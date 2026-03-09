<?php

namespace App\Services;

use App\Models\Patient;
use App\Notifications\Patient\PatientGenericNotification;
use Illuminate\Notifications\DatabaseNotification;

class PatientNotificationService
{
    /**
     * Send a multilingual database notification to a patient.
     *
     * Uses Laravel's standard Notifiable + database channel so the record is
     * stored in the `notifications` table with notifiable_type = Patient::class.
     *
     * @param  array  $title  ['en' => ..., 'ar' => ..., 'fr' => ...]
     * @param  array  $body   ['en' => ..., 'ar' => ..., 'fr' => ...]
     * @param  array  $data   Extra payload stored under the 'data' key
     */
    public static function send(
        Patient $patient,
        string $type,
        array $title,
        array $body,
        array $data = [],
        ?string $actionUrl = null,
    ): ?DatabaseNotification {
        $patient->notify(new PatientGenericNotification($type, $title, $body, $data, $actionUrl));

        /** @var DatabaseNotification|null */
        return $patient->notifications()->latest()->first();
    }
}
