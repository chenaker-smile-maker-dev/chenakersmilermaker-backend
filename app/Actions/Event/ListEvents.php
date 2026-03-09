<?php

namespace App\Actions\Event;

use App\Models\Event;
use App\Utils\GetModelMultilangAttribute;
use App\Utils\MediaHelper;

class ListEvents
{
    public function handle(int $page = 1, int $perPage = 10, ?string $type = null)
    {
        $query = Event::query();

        if ($type === 'archive') {
            $query->archived();
        } elseif ($type === 'happening') {
            $query->happening();
        } elseif ($type === 'future') {
            $query->future();
        }

        $events = $query->paginate($perPage, ['*'], 'page', $page);

        return [
            'data' => collect($events->items())->map(fn (Event $event) => $this->formatEvent($event))->values()->toArray(),
            'pagination' => [
                'total' => $events->total(),
                'per_page' => $events->perPage(),
                'current_page' => $events->currentPage(),
                'last_page' => $events->lastPage(),
                'from' => $events->firstItem(),
                'to' => $events->lastItem(),
            ],
        ];
    }

    private function formatEvent(Event $event): array
    {
        return [
            'id' => $event->id,
            'name' => GetModelMultilangAttribute::get($event, 'title'),
            'date' => $event->date->format('Y-m-d'),
            'time' => $event->time?->format('H:i'),
            'location' => GetModelMultilangAttribute::get($event, 'location'),
            'speakers' => GetModelMultilangAttribute::get($event, 'speakers'),
            'about_event' => GetModelMultilangAttribute::get($event, 'about_event'),
            'what_to_expect' => GetModelMultilangAttribute::get($event, 'what_to_expect'),
            'pictures' => MediaHelper::collection($event, 'gallery'),
            'status' => $event->status,
        ];
    }
}

