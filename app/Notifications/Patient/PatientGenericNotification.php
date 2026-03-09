<?php

namespace App\Notifications\Patient;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Generic patient database notification with multilingual (en/ar/fr) title & body.
 *
 * Stored in the standard Laravel `notifications` table. The `data` JSON column
 * holds: type, title{en,ar,fr}, body{en,ar,fr}, data{}, action_url.
 */
class PatientGenericNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $type,
        private readonly array  $title,
        private readonly array  $body,
        private readonly array  $extra = [],
        private readonly ?string $actionUrl = null,
    ) {}

    /** @return string[] */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'       => $this->type,
            'title'      => $this->title,   // ['en' => ..., 'ar' => ..., 'fr' => ...]
            'body'       => $this->body,    // ['en' => ..., 'ar' => ..., 'fr' => ...]
            'data'       => $this->extra,
            'action_url' => $this->actionUrl,
        ];
    }
}
