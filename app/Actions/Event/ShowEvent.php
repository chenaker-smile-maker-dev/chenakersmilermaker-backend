<?php

namespace App\Actions\Event;

use App\Models\Event;
use App\Utils\GetModelMultilangAttribute;
use App\Utils\MediaHelper;

class ShowEvent
{
    public function handle(Event $event): array
    {
        return [
            'id' => $event->id,
            'name' => GetModelMultilangAttribute::get($event, 'title'),
            'description' => GetModelMultilangAttribute::get($event, 'description'),
            'date' => $event->date->format('Y-m-d'),
            'time' => $event->time?->format('H:i'),
            'location' => GetModelMultilangAttribute::get($event, 'location'),
            'speakers' => GetModelMultilangAttribute::get($event, 'speakers'),
            'about_event' => GetModelMultilangAttribute::get($event, 'about_event'),
            'what_to_expect' => GetModelMultilangAttribute::get($event, 'what_to_expect'),
            'pictures' => MediaHelper::collection($event, 'gallery'),
            'status' => $event->status,
            'is_archived' => $event->is_archived,
            'created_at' => $event->created_at->toIso8601String(),
        ];
    }
}

