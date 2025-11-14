<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Patient\Booking\CheckAvailabilitySlot;
use App\Actions\Patient\Booking\CreateAppointment;
use App\Http\Controllers\Api\BaseController;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Service;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;

#[Group('Booking', weight: 4)]
class BookingController extends BaseController
{
    /**
     * Check if a specific time slot is available for booking
     */
    public function checkAvailability(Doctor $doctor, Service $service, Request $request, CheckAvailabilitySlot $checkAvailabilitySlot)
    {
        try {
            // Validate request body
            $validated = $request->validate([
                'date' => 'required|date_format:Y-m-d',
                'start_time' => 'required|date_format:H:i',
            ]);

            $data = $checkAvailabilitySlot->handle(
                $doctor,
                $service,
                $validated['date'],
                $validated['start_time']
            );

            return $this->sendResponse($data);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 422);
        }
    }

    /**
     * Book an appointment
     */
    public function bookAppointment(Doctor $doctor, Service $service, Request $request, CreateAppointment $createAppointment)
    {
        try {
            // Validate request body
            $validated = $request->validate([
                'date' => 'required|date_format:Y-m-d',
                'start_time' => 'required|date_format:H:i',
            ]);

            // Get authenticated patient
            $user = $request->user();
            $patient = Patient::where('user_id', $user->id)->first();

            if (!$patient) {
                return $this->sendError('Patient profile not found', 404);
            }

            $data = $createAppointment->handle(
                $doctor,
                $service,
                $patient,
                $validated['date'],
                $validated['start_time']
            );

            if (!$data['success']) {
                return $this->sendError($data['error'], [], 422);
            }

            return $this->sendResponse($data, 'Appointment booked successfully');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 422);
        }
    }
}
