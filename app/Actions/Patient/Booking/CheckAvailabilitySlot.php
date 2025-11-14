<?php

namespace App\Actions\Patient\Booking;

use App\Models\Doctor;
use App\Models\Service;
use Carbon\Carbon;

class CheckAvailabilitySlot
{
    /**
     * Check if a specific time slot is available for booking
     *
     * @param Doctor $doctor
     * @param Service $service
     * @param string $date Date in Y-m-d format
     * @param string $startTime Start time in H:i format
     * @return array
     */
    public function handle(Doctor $doctor, Service $service, string $date, string $startTime): array
    {
        try {
            // Validate inputs
            $this->validateInputs($doctor, $service, $date, $startTime);

            // Get service duration
            $serviceDuration = $service->duration ?? 30;

            // Extract availability info (hours and days) from doctor's schedule
            $availabilityInfo = $this->getAvailabilityInfo($doctor);

            if (!$availabilityInfo) {
                return $this->unavailableResponse('Doctor has no availability scheduled');
            }

            // Check if the requested date matches the selected days
            $checkDate = Carbon::createFromFormat('Y-m-d', $date);
            $dayOfWeek = $checkDate->dayOfWeek; // 0=Sunday, 1=Monday, etc.

            if (!in_array($dayOfWeek, $availabilityInfo['days'])) {
                return $this->unavailableResponse('Doctor is not available on this day of the week');
            }

            // Check if the requested time is within availability window
            $isWithinWindow = $this->isTimeWithinWindow(
                $startTime,
                $serviceDuration,
                $availabilityInfo['dayStart'],
                $availabilityInfo['dayEnd']
            );

            if (!$isWithinWindow) {
                return $this->unavailableResponse('Requested time is outside doctor\'s availability window');
            }

            // Check if the slot is available (no conflicts)
            $slots = $doctor->getAvailableSlots(
                date: $date,
                dayStart: $availabilityInfo['dayStart'],
                dayEnd: $availabilityInfo['dayEnd'],
                slotDuration: $serviceDuration
            );

            $isAvailable = false;
            foreach ($slots as $slot) {
                if ($slot['start_time'] === $startTime && $slot['is_available']) {
                    $isAvailable = true;
                    break;
                }
            }

            if (!$isAvailable) {
                return $this->unavailableResponse('Requested slot is not available or already booked');
            }

            // Calculate end time
            $endTime = $this->calculateEndTime($startTime, $serviceDuration);

            return $this->availableResponse($doctor, $service, $date, $startTime, $endTime, $serviceDuration);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Validate doctor, service, date, and time
     *
     * @throws \Exception
     */
    private function validateInputs(Doctor $doctor, Service $service, string $date, string $startTime): void
    {
        if (!$service->active) {
            throw new \Exception('Service is not active');
        }

        if (!$doctor->services()->where('service_id', $service->id)->exists()) {
            throw new \Exception('Doctor does not provide this service');
        }

        // Validate date format and that it's not in the past
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
     * Check if time + duration is within availability window
     */
    private function isTimeWithinWindow(string $startTime, int $duration, string $dayStart, string $dayEnd): bool
    {
        $endTime = $this->calculateEndTime($startTime, $duration);

        // Convert to minutes for comparison
        $startMinutes = $this->timeToMinutes($startTime);
        $endMinutes = $this->timeToMinutes($endTime);
        $dayStartMinutes = $this->timeToMinutes($dayStart);
        $dayEndMinutes = $this->timeToMinutes($dayEnd);

        return $startMinutes >= $dayStartMinutes && $endMinutes <= $dayEndMinutes;
    }

    /**
     * Calculate end time based on start time and duration
     */
    private function calculateEndTime(string $startTime, int $duration): string
    {
        $carbon = Carbon::createFromFormat('H:i', $startTime);
        return $carbon->addMinutes($duration)->format('H:i');
    }

    /**
     * Convert time string to minutes
     */
    private function timeToMinutes(string $time): int
    {
        [$hours, $minutes] = explode(':', $time);
        return ((int) $hours * 60) + (int) $minutes;
    }

    /**
     * Success response
     */
    private function availableResponse(
        Doctor $doctor,
        Service $service,
        string $date,
        string $startTime,
        string $endTime,
        int $duration
    ): array {
        return [
            'available' => true,
            'doctor_id' => $doctor->id,
            'doctor_name' => $doctor->name,
            'service_id' => $service->id,
            'service_name' => $service->name,
            'date' => $date,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'duration_minutes' => $duration,
            'price' => $service->price ?? 0,
        ];
    }

    /**
     * Unavailable response
     */
    private function unavailableResponse(string $reason): array
    {
        return [
            'available' => false,
            'reason' => $reason,
        ];
    }

    /**
     * Error response
     */
    private function errorResponse(string $message): array
    {
        return [
            'available' => false,
            'error' => $message,
        ];
    }
}
