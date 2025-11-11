<?php

namespace App\Actions\Patient\Booking;

use App\Models\Doctor;
use App\Utils\GetModelMultilangAttribute;

class ShowDoctor
{
    public function handle(Doctor $doctor)
    {
        $doctor->load('services');

        return [
            'id' => $doctor->id,
            'name' => GetModelMultilangAttribute::get($doctor, 'name'),
            'specialty' => GetModelMultilangAttribute::get($doctor, 'specialty'),
            // 'email' => $doctor->email ?? null,
            // 'phone' => $doctor->phone ?? null,
            'services_count' => $doctor->services->count(),
            'services' => $doctor->services->map(function ($service) {
                return [
                    'id' => $service->id,
                    'name' => GetModelMultilangAttribute::get($service, 'name'),
                    'price' => $service->price,
                    'duration' => $service->duration,
                    'active' => $service->active
                ];
            })->toArray()
        ];
    }
}
