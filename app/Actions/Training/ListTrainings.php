<?php

namespace App\Actions\Training;

class ListTrainings
{
    public function handle(int $page = 1, int $perPage = 10)
    {
        $trainings = \App\Models\Training::paginate($perPage, ['*'], 'page', $page);

        return [
            'data' => $trainings->items(),
            'pagination' => [
                'total' => $trainings->total(),
                'per_page' => $trainings->perPage(),
                'current_page' => $trainings->currentPage(),
                'last_page' => $trainings->lastPage(),
                'from' => $trainings->firstItem(),
                'to' => $trainings->lastItem(),
            ],
        ];
    }
}
