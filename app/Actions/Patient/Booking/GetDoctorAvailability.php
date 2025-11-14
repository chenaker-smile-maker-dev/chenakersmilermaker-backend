<?php

namespace App\Actions\Patient\Booking;

use App\Models\Doctor;
use App\Models\Service;

class GetDoctorAvailability
{
    /**
     * Handle getting doctor's next available slot for a service
     *
     * @param Doctor $doctor
     * @param Service $service
     * @return array
     */
    public function handle(Doctor $doctor, Service $service): array
    {
        // Validate inputs
        $this->validateInputs($doctor, $service);

        // Get service duration
        $serviceDuration = $service->duration ?? 30;

        // Extract availability hours from doctor's schedule
        $availabilityWindow = $this->getAvailabilityWindow($doctor);

        if (!$availabilityWindow) {
            return $this->noAvailabilityResponse($doctor, $service);
        }

        // Find the next available slot within the availability window
        $nextSlot = $this->findNextAvailableSlot(
            $doctor,
            $serviceDuration,
            $availabilityWindow['dayStart'],
            $availabilityWindow['dayEnd']
        );

        return $this->successResponse($doctor, $service, $serviceDuration, $nextSlot);
    }

    /**
     * Validate doctor and service
     *
     * @throws \Exception
     */
    private function validateInputs(Doctor $doctor, Service $service): void
    {
        if (!$service->active) {
            throw new \Exception('Service is not active');
        }

        if (!$doctor->services()->where('service_id', $service->id)->exists()) {
            throw new \Exception('Doctor does not provide this service');
        }
    }

    /**
     * Get doctor's availability window (start and end times)
     * Returns null if no availability found
     */
    private function getAvailabilityWindow(Doctor $doctor): ?array
    {
        $availabilitySchedules = $doctor->availabilitySchedules()
            ->active()
            ->get();

        if ($availabilitySchedules->isEmpty()) {
            return null;
        }

        // Extract times from the first availability schedule's first period
        $firstSchedule = $availabilitySchedules->first();
        $periods = $firstSchedule->periods()->get();

        if ($periods->isEmpty()) {
            return null;
        }

        $firstPeriod = $periods->first();

        return [
            'dayStart' => $firstPeriod->start_time ?? '09:00',
            'dayEnd' => $firstPeriod->end_time ?? '17:00',
        ];
    }

    /**
     * Find the next available slot within availability window
     */
    private function findNextAvailableSlot(
        Doctor $doctor,
        int $duration,
        string $dayStart,
        string $dayEnd
    ): ?array {
        $checkDate = now();

        // Check up to 30 days in the future
        for ($i = 0; $i < 30; $i++) {
            $dateString = $checkDate->format('Y-m-d');

            // Get all slots for this day within the doctor's availability window
            $slots = $doctor->getAvailableSlots(
                date: $dateString,
                dayStart: $dayStart,
                dayEnd: $dayEnd,
                slotDuration: $duration
            );

            // Find the first truly available slot
            foreach ($slots as $slot) {
                if ($slot['is_available']) {
                    return array_merge($slot, ['date' => $dateString]);
                }
            }

            $checkDate = $checkDate->addDay();
        }

        return null;
    }

    /**
     * Response when no availability is found
     */
    private function noAvailabilityResponse(Doctor $doctor, Service $service): array
    {
        return [
            'id' => $doctor->id,
            'doctor_name' => $doctor->name,
            'service_id' => $service->id,
            'service_name' => $service->name,
            'service_duration_minutes' => $service->duration ?? 30,
            'is_service_active' => $service->active,
            'next_available_slot' => null,
            'message' => 'Doctor has no availability scheduled',
        ];
    }

    /**
     * Success response with availability slot
     */
    private function successResponse(
        Doctor $doctor,
        Service $service,
        int $serviceDuration,
        ?array $nextSlot
    ): array {
        return [
            'id' => $doctor->id,
            'doctor_name' => $doctor->name,
            'service_id' => $service->id,
            'service_name' => $service->name,
            'service_duration_minutes' => $serviceDuration,
            'is_service_active' => $service->active,
            'next_available_slot' => $nextSlot,
        ];
    }
}
