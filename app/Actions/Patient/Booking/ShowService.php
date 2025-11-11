<?php

namespace App\Actions\Patient\Booking;

use App\Http\Resources\DoctorResource;
use App\Http\Resources\ServiceResource;
use App\Models\Service;

class ShowService
{
    public function handle(Service $service)
    {
        $service->load('doctors');

        return [
            'service' => ServiceResource::make($service),
            'doctors_count' => $service->doctors->count(),
            'doctors' => DoctorResource::collection($service->doctors),
        ];
    }
}
