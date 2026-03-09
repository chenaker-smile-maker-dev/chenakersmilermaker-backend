<?php

namespace App\Actions\Patient\Appointment;

use App\Enums\Appointment\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use App\Notifications\Admin\AppointmentRescheduleRequested;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;

class RequestAppointmentReschedule
{
    public function handle(
        Appointment $appointment,
        Patient $patient,
        string $reason,
        string $newDate,
        string $newStartTime
    ): array {
        // Verify ownership
        if ($appointment->patient_id !== $patient->id) {
            throw new \Exception(__('api.appointment_not_yours'), 403);
        }

        // Verify status allows reschedule
        if (!in_array($appointment->status, [AppointmentStatus::PENDING, AppointmentStatus::CONFIRMED])) {
            throw new \Exception(
                __('api.invalid_status_for_reschedule', ['status' => $appointment->status->value]),
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

        // Convert new_date from d-m-Y to Y-m-d
        $parsedDate = Carbon::createFromFormat('d-m-Y', $newDate)->format('Y-m-d');

        // Calculate new end time based on service duration
        $serviceDuration = $appointment->service->duration ?? 30;
        $newFrom = Carbon::createFromFormat('Y-m-d H:i', "{$parsedDate} {$newStartTime}");
        $newTo = $newFrom->copy()->addMinutes($serviceDuration);

        $appointment->update([
            'original_from' => $appointment->from,
            'original_to' => $appointment->to,
            'requested_new_from' => $newFrom,
            'requested_new_to' => $newTo,
            'change_request_status' => 'pending_reschedule',
            'reschedule_reason' => $reason,
        ]);

        // Notify all admins
        $admins = User::all();
        Notification::send($admins, new AppointmentRescheduleRequested($appointment->fresh()));

        return [
            'id' => $appointment->id,
            'status' => $appointment->status->value,
            'change_request_status' => $appointment->change_request_status,
            'requested_new_date' => $newFrom->format('Y-m-d'),
            'requested_new_time' => $newFrom->format('H:i'),
        ];
    }
}
