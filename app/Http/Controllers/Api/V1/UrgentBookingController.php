<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Patient\UrgentBooking\ListPatientUrgentBookings;
use App\Actions\Patient\UrgentBooking\SubmitUrgentBooking;
use App\Http\Controllers\BaseController;
use App\Models\UrgentBooking;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

#[Group('(patient) Urgent Bookings', weight: 7)]
class UrgentBookingController extends BaseController
{
    /**
     * Submit an urgent booking (no auth required).
     */
    public function submit(Request $request, SubmitUrgentBooking $action): JsonResponse
    {
        $validated = $request->validate([
            'patient_name' => 'required|string|max:255',
            'patient_phone' => 'required|string|max:20',
            'patient_email' => 'nullable|email',
            'reason' => 'required|string|min:10|max:1000',
            'description' => 'nullable|string|max:2000',
            'preferred_datetime' => 'nullable|date_format:Y-m-d H:i',
        ]);

        $patient = auth('sanctum')->user()?->patient ?? null;

        $booking = $action->handle($validated, $patient);

        return $this->sendResponse([
            'id' => $booking->id,
            'patient_name' => $booking->patient_name,
            'patient_phone' => $booking->patient_phone,
            'status' => $booking->status->value,
            'created_at' => $booking->created_at->toIso8601String(),
        ], __('api.urgent_booking_submitted'), 201);
    }

    /**
     * List the authenticated patient's urgent bookings.
     */
    public function myBookings(Request $request, ListPatientUrgentBookings $action): JsonResponse
    {
        $patient = $request->user()->patient;

        return $this->sendResponse(
            $action->handle($patient),
            __('api.success')
        );
    }

    /**
     * Show a single urgent booking (must belong to authenticated patient).
     */
    public function show(Request $request, UrgentBooking $urgentBooking): JsonResponse
    {
        $patient = $request->user()->patient;

        if ($urgentBooking->patient_id !== $patient->id) {
            return $this->sendError(__('api.not_found'), [], 404);
        }

        return $this->sendResponse([
            'id' => $urgentBooking->id,
            'reason' => $urgentBooking->reason,
            'description' => $urgentBooking->description,
            'status' => $urgentBooking->status->value,
            'patient_name' => $urgentBooking->patient_name,
            'patient_phone' => $urgentBooking->patient_phone,
            'preferred_datetime' => $urgentBooking->preferred_datetime?->toIso8601String(),
            'scheduled_datetime' => $urgentBooking->scheduled_datetime?->toIso8601String(),
            'admin_notes' => $urgentBooking->admin_notes,
            'assigned_doctor' => $urgentBooking->assignedDoctor ? [
                'id' => $urgentBooking->assignedDoctor->id,
                'name' => $urgentBooking->assignedDoctor->display_name,
            ] : null,
            'created_at' => $urgentBooking->created_at->toIso8601String(),
        ], __('api.success'));
    }
}
