<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Patient\UrgentBooking\ListPatientUrgentBookings;
use App\Actions\Patient\UrgentBooking\SubmitUrgentBooking;
use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\UrgentBookingResource;
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
            'patient_name'       => 'required|string|max:255',
            'patient_phone'      => 'required|string|max:20',
            'patient_email'      => 'nullable|email',
            'reason'             => 'required|string|min:10|max:1000',
            'description'        => 'nullable|string|max:2000',
            'preferred_datetime' => 'nullable|date_format:Y-m-d H:i',
        ]);

        $patient = auth('sanctum')->user() ?? null;

        $booking = $action->handle($validated, $patient);

        return $this->sendResponse(UrgentBookingResource::make($booking), 'api.urgent_booking_submitted', 201);
    }

    /**
     * List the authenticated patient's urgent bookings.
     */
    public function myBookings(Request $request, ListPatientUrgentBookings $action): JsonResponse
    {
        return $this->sendResponse(
            UrgentBookingResource::collection($action->handle($request->user())),
            'api.success'
        );
    }

    /**
     * Show a single urgent booking (must belong to authenticated patient).
     */
    public function show(Request $request, UrgentBooking $urgentBooking): JsonResponse
    {
        if ($urgentBooking->patient_id !== $request->user()->id) {
            return $this->sendError('api.not_found', [], 404);
        }

        $urgentBooking->load('assignedDoctor');

        return $this->sendResponse(UrgentBookingResource::make($urgentBooking), 'api.success');
    }
}
