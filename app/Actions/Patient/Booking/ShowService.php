<?php

namespace App\Actions\Patient\Booking;

use App\Models\Service;
use App\Utils\GetModelMultilangAttribute;

class ShowService
{
    public function handle(Service $service)
    {
        $service->load('doctors');
        return [
            'id' => $service->id,
            'name' => GetModelMultilangAttribute::get($service, 'name'),
            'price' => $service->price,
            'duration' => $service->duration,
            'active' => $service->active,
            'doctors_count' => $service->doctors->count(),
            'doctors' => $service->doctors->map(function ($doctor) {
                return [
                    'id' => $doctor->id,
                    'name' => GetModelMultilangAttribute::get($doctor, 'name'),
                    'specialty' => GetModelMultilangAttribute::get($doctor, 'specialty'),
                    // 'email' => $doctor->email ?? null,
                    // 'phone' => $doctor->phone ?? null,
                    'thumbnail_url' => $doctor->thumb_image,
                ];
            })->toArray()
        ];
    }
}
