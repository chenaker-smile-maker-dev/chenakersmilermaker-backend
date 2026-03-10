<?php

namespace App\Actions\Patient\Appointment;

use App\Models\Appointment;
use App\Models\Patient;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListPatientAppointments
{
    public function handle(Patient $patient, array $filters = [], int $page = 1, int $perPage = 10): LengthAwarePaginator
    {
        $query = Appointment::with(['doctor', 'service'])
            ->where('patient_id', $patient->id);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['from_date'])) {
            $query->whereDate('from', '>=', $filters['from_date']);
        }

        if (! empty($filters['to_date'])) {
            $query->whereDate('from', '<=', $filters['to_date']);
        }

        return $query->orderByDesc('from')->paginate($perPage, ['*'], 'page', $page);
    }
}
