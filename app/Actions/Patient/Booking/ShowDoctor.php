<?php

namespace App\Actions\Patient\Booking;

use App\Http\Resources\DoctorResource;
use App\Http\Resources\ServiceResource;
use App\Models\Doctor;

class ShowDoctor
{
    public function handle(Doctor $doctor)
    {
        $doctor->load('services');

        return [
            'doctor' => DoctorResource::make($doctor),
            'services_count' => $doctor->services->count(),
            'services' => ServiceResource::collection($doctor->services),
        ];
    }
}
