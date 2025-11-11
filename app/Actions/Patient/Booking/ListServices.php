<?php

namespace App\Actions\Patient\Booking;

use App\Http\Resources\ServiceResource;
use App\Models\Service;

class ListServices
{
    public function handle(int $page = 1, int $per_page = 15)
    {
        $query = Service::query();
        $total = $query->count();

        $services = $query->forPage($page, $per_page)->get();

        return [
            'total' => $total,
            'per_page' => $per_page,
            'current_page' => $page,
            'last_page' => ceil($total / $per_page),
            'services' => ServiceResource::collection($services)
        ];
    }
}
