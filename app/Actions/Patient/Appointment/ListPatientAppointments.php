<?php

namespace App\Actions\Patient\Appointment;

use App\Models\Appointment;
use App\Models\Patient;
use App\Utils\GetModelMultilangAttribute;
use App\Utils\MediaHelper;

class ListPatientAppointments
{
    public function handle(Patient $patient, array $filters = [], int $page = 1, int $perPage = 10): array
    {
        $query = Appointment::with(['doctor', 'service'])
            ->where('patient_id', $patient->id);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['from_date'])) {
            $query->whereDate('from', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate('from', '<=', $filters['to_date']);
        }

        $appointments = $query->orderByDesc('from')->paginate($perPage, ['*'], 'page', $page);

        return [
            'data' => $appointments->map(function (Appointment $appointment) {
                return $this->formatAppointment($appointment);
            }),
            'pagination' => [
                'current_page' => $appointments->currentPage(),
                'per_page' => $appointments->perPage(),
                'total' => $appointments->total(),
                'last_page' => $appointments->lastPage(),
            ],
        ];
    }

    private function formatAppointment(Appointment $appointment): array
    {
        $doctor = $appointment->doctor;
        $service = $appointment->service;

        return [
            'id' => $appointment->id,
            'doctor' => $doctor ? [
                'id' => $doctor->id,
                'name' => GetModelMultilangAttribute::get($doctor, 'name'),
                'specialty' => GetModelMultilangAttribute::get($doctor, 'specialty'),
                'image' => MediaHelper::single($doctor, 'doctor_photo'),
                'phone' => $doctor->phone,
            ] : null,
            'service' => $service ? [
                'id' => $service->id,
                'name' => GetModelMultilangAttribute::get($service, 'name'),
                'price' => $service->price,
                'duration' => $service->duration,
            ] : null,
            'date' => $appointment->from->format('Y-m-d'),
            'start_time' => $appointment->from->format('H:i'),
            'end_time' => $appointment->to->format('H:i'),
            'status' => $appointment->status->value,
            'change_request_status' => $appointment->change_request_status,
            'price' => $appointment->price,
            'created_at' => $appointment->created_at->toIso8601String(),
        ];
    }
}
