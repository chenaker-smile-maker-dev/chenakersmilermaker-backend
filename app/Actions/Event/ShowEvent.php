<?php

namespace App\Actions\Event;

use App\Models\Event;

class ShowEvent
{
    public function handle(Event $event): Event
    {
        return $event;
    }
}
