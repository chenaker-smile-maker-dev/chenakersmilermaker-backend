<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Patient\Booking\CheckAvailabilitySlot;
use App\Actions\Patient\Booking\CreateAppointment;
use App\Http\Controllers\Api\BaseController;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Service;
use Dedoc\Scramble\Attributes\BodyParameter;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;

#[Group('Booking', weight: 4)]
class BookingController extends BaseController
{
    /**
     * Check if a specific time slot is available for booking
     *
     * Takes a date and start time, checks if the doctor has an available slot for the service duration at that time.
     * Respects doctor's availability schedule (days of week and hours).
     */
    #[BodyParameter('date', description: 'Date for the appointment in d-m-Y format', type: 'string', format: 'date', example: '15-12-2025', required: true)]
    #[BodyParameter('start_time', description: 'Start time for the appointment in H:i format (24-hour)', type: 'string', format: 'time', example: '14:30', required: true)]
    public function checkAvailability(Doctor $doctor, Service $service, Request $request, CheckAvailabilitySlot $checkAvailabilitySlot)
    {
        try {
            // Validate request body
            $validated = $request->validate([
                'date' => 'required|date_format:d-m-Y',
                'start_time' => 'required|date_format:H:i',
            ]);

            // Convert date from d-m-Y to Y-m-d format for the action
            $dateObj = \Carbon\Carbon::createFromFormat('d-m-Y', $validated['date']);
            $formattedDate = $dateObj->format('Y-m-d');

            $data = $checkAvailabilitySlot->handle(
                $doctor,
                $service,
                $formattedDate,
                $validated['start_time']
            );

            return $this->sendResponse($data);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 422);
        }
    }

    /**
     * Book an appointment
     *
     * Creates an appointment for the authenticated patient with the specified doctor and service.
     * The time slot must be available and within the doctor's availability schedule.
     * Also creates a block in Zap to prevent double-booking.
     */
    #[BodyParameter('date', description: 'Date for the appointment in d-m-Y format', type: 'string', format: 'date', example: '15-12-2025', required: true)]
    #[BodyParameter('start_time', description: 'Start time for the appointment in H:i format (24-hour)', type: 'string', format: 'time', example: '14:30', required: true)]
    public function bookAppointment(Doctor $doctor, Service $service, Request $request, CreateAppointment $createAppointment)
    {
        try {
            // Validate request body
            $validated = $request->validate([
                'date' => 'required|date_format:d-m-Y',
                'start_time' => 'required|date_format:H:i',
            ]);

            // Convert date from d-m-Y to Y-m-d format for the action
            $dateObj = \Carbon\Carbon::createFromFormat('d-m-Y', $validated['date']);
            $formattedDate = $dateObj->format('Y-m-d');

            // Get authenticated patient
            $patient = $request->user();
            // $patient = Patient::where('id', $user->id)->first();

            if (! $patient) {
                return $this->sendError('Patient profile not found', 404);
            }

            $data = $createAppointment->handle(
                $doctor,
                $service,
                $patient,
                $formattedDate,
                $validated['start_time']
            );

            if (! $data['success']) {
                return $this->sendError($data['error'], [], 422);
            }

            return $this->sendResponse($data, 'Appointment booked successfully');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 422);
        }
    }
}
