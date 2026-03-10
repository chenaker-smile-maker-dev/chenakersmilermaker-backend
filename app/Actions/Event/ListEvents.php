<?php

namespace App\Actions\Event;

use App\Models\Event;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListEvents
{
    public function handle(int $page = 1, int $perPage = 10, ?string $type = null): LengthAwarePaginator
    {
        $query = Event::query();

        if ($type === 'archive') {
            $query->archived();
        } elseif ($type === 'happening') {
            $query->happening();
        } elseif ($type === 'future') {
            $query->future();
        }

        return $query->latest('date')->paginate($perPage, ['*'], 'page', $page);
    }
}
