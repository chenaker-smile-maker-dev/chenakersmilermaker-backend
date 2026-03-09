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

        $events = $query->latest('date')->paginate($perPage, ['*'], 'page', $page);

        return [
            'data' => $events->getCollection()->map(fn(Event $event) => [
                'id' => $event->id,
                'name' => GetModelMultilangAttribute::get($event, 'title'),
                'description' => GetModelMultilangAttribute::get($event, 'description'),
                'date' => $event->date?->toDateString(),
                'time' => $event->time,
                'location' => GetModelMultilangAttribute::get($event, 'location'),
                'speakers' => GetModelMultilangAttribute::get($event, 'speakers'),
                'about_event' => GetModelMultilangAttribute::get($event, 'about_event'),
                'what_to_expect' => GetModelMultilangAttribute::get($event, 'what_to_expect'),
                'pictures' => MediaHelper::collection($event, 'gallery'),
                'status' => $event->status,
            ])->values()->toArray(),
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
}
