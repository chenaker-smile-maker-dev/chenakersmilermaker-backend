<?php

namespace App\Actions\Patient\UrgentBooking;

use App\Models\Patient;

class ListPatientUrgentBookings
{
    public function handle(Patient $patient): array
    {
        $bookings = $patient->urgentBookings()
            ->with('assignedDoctor')
            ->latest()
            ->get();

        return $bookings->map(function ($booking) {
            return [
                'id' => $booking->id,
                'reason' => $booking->reason,
                'description' => $booking->description,
                'status' => $booking->status->value,
                'patient_name' => $booking->patient_name,
                'patient_phone' => $booking->patient_phone,
                'preferred_datetime' => $booking->preferred_datetime?->toIso8601String(),
                'scheduled_datetime' => $booking->scheduled_datetime?->toIso8601String(),
                'admin_notes' => $booking->admin_notes,
                'assigned_doctor' => $booking->assignedDoctor ? [
                    'id' => $booking->assignedDoctor->id,
                    'name' => $booking->assignedDoctor->display_name,
                ] : null,
                'created_at' => $booking->created_at->toIso8601String(),
            ];
        })->toArray();
    }
}
