<?php

namespace App\Actions\Patient\Booking;

use App\Models\Doctor;
use App\Models\Service;
use Illuminate\Support\Facades\Log;

class GetDoctorAvailability
{
    /**
     * Handle getting doctor's next available slot for a service
     */
    public function handle(Doctor $doctor, Service $service): array
    {
        // Validate inputs
        $this->validateInputs($doctor, $service);

        // Get service duration
        $serviceDuration = $service->duration ?? 30;

        // Extract availability hours and days from doctor's schedule
        $availabilityInfo = $this->getAvailabilityInfo($doctor);

        if (! $availabilityInfo) {
            return $this->noAvailabilityResponse($doctor, $service);
        }

        // Find the next available slot within the availability window and days
        $nextSlot = $this->findNextAvailableSlot(
            $doctor,
            $serviceDuration,
            $availabilityInfo['dayStart'],
            $availabilityInfo['dayEnd'],
            $availabilityInfo['days']
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
        if (! $service->active) {
            throw new \Exception('Service is not active');
        }

        if (! $doctor->services()->where('service_id', $service->id)->exists()) {
            throw new \Exception('Doctor does not provide this service');
        }
    }

    /**
     * Get doctor's availability info (start/end times and days of week)
     * Returns null if no availability found
     */
    private function getAvailabilityInfo(Doctor $doctor): ?array
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

        // Get days of week from frequency_config
        $frequencyConfig = $firstSchedule->frequency_config ?? [];
        $days = $frequencyConfig['days'] ?? [];

        // Convert day names to numeric values (0=Sunday, 1=Monday, ..., 6=Saturday)
        $dayMap = [
            'sunday' => 0,
            'monday' => 1,
            'tuesday' => 2,
            'wednesday' => 3,
            'thursday' => 4,
            'friday' => 5,
            'saturday' => 6,
        ];

        $numericDays = [];
        foreach ($days as $day) {
            if (isset($dayMap[$day])) {
                $numericDays[] = $dayMap[$day];
            }
        }

        return [
            'dayStart' => $firstPeriod->start_time ?? '09:00',
            'dayEnd' => $firstPeriod->end_time ?? '17:00',
            'days' => $numericDays, // Array of numeric day values
        ];
    }

    /**
     * Find the next available slot within availability window and days
     */
    private function findNextAvailableSlot(
        Doctor $doctor,
        int $duration,
        string $dayStart,
        string $dayEnd,
        array $allowedDays
    ): ?array {
        $checkDate = now();

        // Check up to 30 days in the future
        for ($i = 0; $i < 30; $i++) {
            $dayOfWeek = $checkDate->dayOfWeek; // 0=Sunday, 1=Monday, etc.

            // Only check if this day is in the allowed days
            if (! in_array($dayOfWeek, $allowedDays)) {
                $checkDate = $checkDate->addDay();

                continue;
            }

            // Skip if this is today and we're already past the availability window
            if ($checkDate->isToday()) {
                $currentTime = now()->format('H:i');
                // If current time is already past or equal to day end, skip to next day
                if ($this->timeToMinutes($currentTime) >= $this->timeToMinutes($dayEnd)) {
                    $checkDate = $checkDate->addDay();

                    continue;
                }
                // Also adjust dayStart to current time if it's later than dayStart
                $adjustedDayStart = max($currentTime, $dayStart);
                if ($this->timeToMinutes($adjustedDayStart) > $this->timeToMinutes($dayStart)) {
                    $dayStart = $adjustedDayStart;
                }
            }

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

            // Reset dayStart for next iterations (was potentially modified for today)
            if ($checkDate->isToday()) {
                $dayStart = $this->getAvailabilityWindow($doctor)['dayStart'] ?? '09:00';
            }

            $checkDate = $checkDate->addDay();
        }

        return null;
    }

    /**
     * Get basic availability window (for resetting dayStart)
     */
    private function getAvailabilityWindow(Doctor $doctor): ?array
    {
        $availabilitySchedules = $doctor->availabilitySchedules()
            ->active()
            ->get();

        if ($availabilitySchedules->isEmpty()) {
            return null;
        }

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
     * Convert time string to minutes for comparison
     */
    private function timeToMinutes(string $time): int
    {
        Log::info("Converting time to minutes: $time");
        [$hours, $minutes] = explode(':', $time);

        return ((int) $hours * 60) + (int) $minutes;
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
