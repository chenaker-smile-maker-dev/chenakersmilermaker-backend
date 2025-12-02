<?php

namespace App\Actions\Event;

class ListEvents
{
    public function handle(int $page = 1, int $perPage = 10)
    {
        $events = \App\Models\Event::where('is_archived', false)
            ->paginate($perPage, ['*'], 'page', $page);

        return [
            'data' => $events->items(),
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
