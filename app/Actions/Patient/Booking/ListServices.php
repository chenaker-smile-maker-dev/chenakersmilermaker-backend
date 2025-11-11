<?php

namespace App\Actions\Patient\Booking;

use App\Models\Service;
use App\Utils\GetModelMultilangAttribute;

class ListServices
{
    public function handle(int $page = 1, int $per_page = 15)
    {
        $query = Service::query();
        $total = $query->count();

        $services = $query->forPage($page, $per_page)
            ->get()
            ->map(function ($service) {
                return [
                    'id' => $service->id,
                    'name' => GetModelMultilangAttribute::get($service, 'name'),
                    'price' => $service->price,
                    'duration' => $service->duration,
                    'active' => $service->active,
                ];
            });

        return [
            'total' => $total,
            'per_page' => $per_page,
            'current_page' => $page,
            'last_page' => ceil($total / $per_page),
            'data' => $services
        ];
    }
}
