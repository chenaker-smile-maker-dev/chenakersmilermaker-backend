<?php

namespace App\Actions\Patient\UrgentBooking;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Collection;

class ListPatientUrgentBookings
{
    public function handle(Patient $patient): Collection
    {
        return $patient->urgentBookings()
            ->with('assignedDoctor')
            ->latest()
            ->get();
    }
}
