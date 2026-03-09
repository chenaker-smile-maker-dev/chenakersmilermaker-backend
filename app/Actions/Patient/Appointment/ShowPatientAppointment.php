<?php

namespace App\Actions\Patient\Appointment;

use App\Models\Appointment;
use App\Models\Patient;
use App\Utils\GetModelMultilangAttribute;
use App\Utils\MediaHelper;

class ShowPatientAppointment
{
    public function handle(Appointment $appointment, Patient $patient): array
    {
        if ($appointment->patient_id !== $patient->id) {
            throw new \Exception(__('api.appointment_not_yours'), 403);
        }

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
                'image' => MediaHelper::single($service, 'image'),
            ] : null,
            'date' => $appointment->from->format('Y-m-d'),
            'start_time' => $appointment->from->format('H:i'),
            'end_time' => $appointment->to->format('H:i'),
            'status' => $appointment->status->value,
            'change_request_status' => $appointment->change_request_status,
            'price' => $appointment->price,
            'admin_notes' => $appointment->admin_notes,
            'cancellation_reason' => $appointment->cancellation_reason,
            'reschedule_reason' => $appointment->reschedule_reason,
            'requested_new_date' => $appointment->requested_new_from?->format('Y-m-d'),
            'requested_new_time' => $appointment->requested_new_from?->format('H:i'),
            'created_at' => $appointment->created_at->toIso8601String(),
            'confirmed_at' => $appointment->confirmed_at?->toIso8601String(),
        ];
    }
}
