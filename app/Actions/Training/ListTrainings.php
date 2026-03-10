<?php

namespace App\Actions\Training;

use App\Models\Training;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListTrainings
{
    public function handle(int $page = 1, int $perPage = 10): LengthAwarePaginator
    {
        return Training::withCount(['approvedReviews'])
            ->paginate($perPage, ['*'], 'page', $page);
    }
}
