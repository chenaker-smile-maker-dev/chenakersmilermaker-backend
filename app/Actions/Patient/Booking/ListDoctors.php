<?php

namespace App\Actions\Patient\Booking;

use App\Models\Doctor;
use App\Utils\GetModelMultilangAttribute;

class ListDoctors
{
    public function handle(int $page = 1, int $per_page = 15)
    {
        $query = Doctor::query();
        $total = $query->count();

        $doctors = $query->forPage($page, $per_page)
            ->get()
            ->map(function ($doctor) {
                return [
                    'id' => $doctor->id,
                    'name' => GetModelMultilangAttribute::get($doctor, 'name'),
                    'specialty' => GetModelMultilangAttribute::get($doctor, 'specialty'),
                    // 'email' => $doctor->email ?? null,
                    // 'phone' => $doctor->phone ?? null,
                    'thumbnail_url' => $doctor->thumb_image,
                ];
            });

        return [
            'total' => $total,
            'per_page' => $per_page,
            'current_page' => $page,
            'last_page' => ceil($total / $per_page),
            'data' => $doctors
        ];
    }
}
