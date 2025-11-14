<?php

namespace App\Actions\Patient\Booking;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Service;
use App\Enums\Appointment\AppointmentStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Zap\Facades\Zap;

class CreateAppointment
{
    /**
     * Create an appointment for a patient with a doctor
     *
     * @param Doctor $doctor
     * @param Service $service
     * @param Patient $patient
     * @param string $date Date in Y-m-d format
     * @param string $startTime Start time in H:i format
     * @return array
     */
    public function handle(Doctor $doctor, Service $service, Patient $patient, string $date, string $startTime): array
    {
        try {
            // Validate inputs
            $this->validateInputs($doctor, $service, $patient, $date, $startTime);

            // Check availability first
            $checkAvailability = new CheckAvailabilitySlot();
            $availabilityCheck = $checkAvailability->handle($doctor, $service, $date, $startTime);

            if (!$availabilityCheck['available']) {
                return $this->errorResponse($availabilityCheck['reason'] ?? $availabilityCheck['error'] ?? 'Slot not available');
            }

            // Create appointment in database
            $appointment = $this->createAppointmentRecord($doctor, $service, $patient, $availabilityCheck);

            // Block this time slot in Zap to prevent double-booking
            $this->blockTimeSlotInZap($doctor, $appointment);

            return $this->successResponse($appointment, $availabilityCheck);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Validate inputs
     *
     * @throws \Exception
     */
    private function validateInputs(Doctor $doctor, Service $service, Patient $patient, string $date, string $startTime): void
    {
        if (!$service->active) {
            throw new \Exception('Service is not active');
        }

        if (!$doctor->services()->where('service_id', $service->id)->exists()) {
            throw new \Exception('Doctor does not provide this service');
        }

        // Validate date format
        try {
            $checkDate = Carbon::createFromFormat('Y-m-d', $date);
            if ($checkDate->startOfDay() < now()->startOfDay()) {
                throw new \Exception('Cannot book appointments in the past');
            }
        } catch (\Exception $e) {
            throw new \Exception('Invalid date format. Use Y-m-d');
        }

        // Validate time format
        if (!preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/', $startTime)) {
            throw new \Exception('Invalid time format. Use H:i (e.g., 14:30)');
        }
    }

    /**
     * Create appointment record in database
     */
    private function createAppointmentRecord(Doctor $doctor, Service $service, Patient $patient, array $availabilityCheck): Appointment
    {
        $fromDateTime = Carbon::createFromFormat('Y-m-d H:i', $availabilityCheck['date'] . ' ' . $availabilityCheck['start_time']);
        $toDateTime = Carbon::createFromFormat('Y-m-d H:i', $availabilityCheck['date'] . ' ' . $availabilityCheck['end_time']);

        $appointment = Appointment::create([
            'doctor_id' => $doctor->id,
            'service_id' => $service->id,
            'patient_id' => $patient->id,
            'from' => $fromDateTime,
            'to' => $toDateTime,
            'price' => $service->price ?? 0,
            'status' => AppointmentStatus::PENDING,
            'metadata' => [
                'booked_at' => now()->toDateTimeString(),
            ],
        ]);

        return $appointment;
    }

    /**
     * Block this time slot in Zap to prevent double-booking
     */
    private function blockTimeSlotInZap(Doctor $doctor, Appointment $appointment): void
    {
        try {
            // Create a block time for this appointment in Zap
            Zap::for($doctor)
                ->named('Appointment #' . $appointment->id)
                ->description('Patient appointment - ' . ($appointment->patient->user?->name ?? $appointment->patient->name ?? 'Patient'))
                ->blocked()
                ->from($appointment->from->format('Y-m-d'))
                ->to($appointment->to->format('Y-m-d'))
                ->addPeriod(
                    $appointment->from->format('H:i'),
                    $appointment->to->format('H:i')
                )
                ->daily()
                ->save();

            // Store Zap block reference in appointment metadata
            $appointment->metadata = array_merge($appointment->metadata ?? [], [
                'zap_block_created' => true,
                'zap_sync_date' => now()->toDateTimeString(),
            ]);
            $appointment->save();
        } catch (\Exception $e) {
            // If Zap sync fails, log it but don't fail the appointment creation
            Log::warning('Failed to create Zap block for appointment ' . $appointment->id . ': ' . $e->getMessage());
        }
    }

    /**
     * Create appointment in Zap calendar
     */
    private function createZapAppointment(Doctor $doctor, Patient $patient, Appointment $appointment, array $availabilityCheck): void
    {
        try {
            $eventTitle = 'Appointment - ' . ($patient->user?->name ?? $patient->name ?? 'Patient');

            // Create block time in Zap for this appointment
            Zap::for($doctor)
                ->named('Appointment - ' . $eventTitle)
                ->description('Service: ' . $availabilityCheck['service_name'])
                ->blocked()
                ->from($appointment->from->format('Y-m-d'))
                ->to($appointment->to->format('Y-m-d'))
                ->addPeriod(
                    $appointment->from->format('H:i'),
                    $appointment->to->format('H:i')
                )
                ->daily()
                ->save();

            // Store Zap event reference in appointment metadata
            $appointment->metadata = array_merge($appointment->metadata ?? [], [
                'zap_event_created' => true,
                'zap_sync_date' => now()->toDateTimeString(),
            ]);
            $appointment->save();
        } catch (\Exception $e) {
            // If Zap sync fails, log it but don't fail the appointment creation
            Log::warning('Failed to create Zap appointment for appointment ' . $appointment->id . ': ' . $e->getMessage());
        }
    }

    /**
     * Success response
     */
    private function successResponse(Appointment $appointment, array $availabilityCheck): array
    {
        return [
            'success' => true,
            'appointment_id' => $appointment->id,
            'appointment_status' => $appointment->status->value,
            'doctor_name' => $appointment->doctor->name,
            'service_name' => $appointment->service->name,
            'date' => $availabilityCheck['date'],
            'start_time' => $availabilityCheck['start_time'],
            'end_time' => $availabilityCheck['end_time'],
            'price' => $appointment->price,
            'message' => 'Appointment created successfully',
        ];
    }

    /**
     * Error response
     */
    private function errorResponse(string $message): array
    {
        return [
            'success' => false,
            'error' => $message,
        ];
    }
}
