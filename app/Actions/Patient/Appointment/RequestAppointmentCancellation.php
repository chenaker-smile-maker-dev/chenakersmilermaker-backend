<?php

namespace App\Actions\Patient\Appointment;

use App\Enums\Appointment\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use App\Notifications\Admin\AppointmentCancellationRequested;
use Illuminate\Support\Facades\Notification;

class RequestAppointmentCancellation
{
    public function handle(Appointment $appointment, Patient $patient, string $reason): array
    {
        // Verify ownership
        if ($appointment->patient_id !== $patient->id) {
            throw new \Exception(__('api.appointment_not_yours'), 403);
        }

        // Verify status allows cancellation
        if (!in_array($appointment->status, [AppointmentStatus::PENDING, AppointmentStatus::CONFIRMED])) {
            throw new \Exception(
                __('api.invalid_status_for_cancellation', ['status' => $appointment->status->value]),
                422
            );
        }

        // Verify no pending change request
        if (
            !empty($appointment->change_request_status) &&
            in_array($appointment->change_request_status, ['pending_cancellation', 'pending_reschedule'])
        ) {
            throw new \Exception(__('api.pending_request_exists'), 422);
        }

        $appointment->update([
            'change_request_status' => 'pending_cancellation',
            'cancellation_reason' => $reason,
        ]);

        // Notify all admins
        $admins = User::all();
        Notification::send($admins, new AppointmentCancellationRequested($appointment->fresh()));

        return [
            'id' => $appointment->id,
            'status' => $appointment->status->value,
            'change_request_status' => $appointment->change_request_status,
        ];
    }
}
