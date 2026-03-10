<?php

namespace App\Actions\Patient\Appointment;

use App\Models\Appointment;
use App\Models\Patient;

class ShowPatientAppointment
{
    public function handle(Appointment $appointment, Patient $patient): Appointment
    {
        if ($appointment->patient_id !== $patient->id) {
            throw new \Exception(__('api.appointment_not_yours'), 403);
        }

        $appointment->load(['doctor', 'service']);

        return $appointment;
    }
}
