<?php

namespace App\Actions\Patient\Booking;

use App\Http\Resources\DoctorResource;
use App\Models\Doctor;

class ListDoctors
{
    public function handle(int $page = 1, int $per_page = 15)
    {
        $query = Doctor::query();
        $total = $query->count();

        $doctors = $query->forPage($page, $per_page)->get();

        return [
            'total' => $total,
            'per_page' => $per_page,
            'current_page' => $page,
            'last_page' => ceil($total / $per_page),
            'doctors' => DoctorResource::collection($doctors)
        ];
    }
}
