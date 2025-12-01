<?php

namespace App\Actions\Patient\Booking;

use App\Models\Doctor;
use App\Models\Service;
use Illuminate\Support\Facades\Log;

class GetDoctorAvailability
{
    private int $slotLimit = 5;

    /**
     * Handle getting doctor's next available slots for a service
     */
    public function handle(Doctor $doctor, Service $service): array
    {
        $this->validateInputs($doctor, $service);

        $serviceDuration = $service->duration ?? 30;
        $availabilityInfo = $this->getAvailabilityInfo($doctor);

        if (! $availabilityInfo) {
            return $this->noAvailabilityResponse($doctor, $service);
        }

        $slots = $this->findNextAvailableSlots(
            $doctor,
            $serviceDuration,
            $availabilityInfo['dayStart'],
            $availabilityInfo['dayEnd'],
            $availabilityInfo['days'],
            $this->slotLimit
        );

        return $this->successResponse($doctor, $service, $serviceDuration, $slots);
    }

    private function validateInputs(Doctor $doctor, Service $service): void
    {
        if (! $service->active) {
            throw new \Exception('Service is not active');
        }

        if (! $doctor->services()->where('service_id', $service->id)->exists()) {
            throw new \Exception('Doctor does not provide this service');
        }
    }

    private function getAvailabilityInfo(Doctor $doctor): ?array
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
        $frequencyConfig = $firstSchedule->frequency_config ?? [];
        $days = $frequencyConfig['days'] ?? [];

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
            'days' => $numericDays,
        ];
    }

    private function findNextAvailableSlots(
        Doctor $doctor,
        int $duration,
        string $dayStart,
        string $dayEnd,
        array $allowedDays,
        int $limit
    ): array {
        if (empty($allowedDays)) {
            return [];
        }

        $slots = [];
        $checkDate = now()->copy();
        $baseDayStart = $dayStart;

        for ($dayIndex = 0; $dayIndex < 30 && count($slots) < $limit; $dayIndex++) {
            $dayOfWeek = $checkDate->dayOfWeek;

            if (! in_array($dayOfWeek, $allowedDays)) {
                $checkDate->addDay();
                continue;
            }

            $dailyStart = $baseDayStart;
            $dateString = $checkDate->format('Y-m-d');

            if ($checkDate->isToday()) {
                $currentTime = now()->format('H:i');
                if ($this->timeToMinutes($currentTime) >= $this->timeToMinutes($dayEnd)) {
                    $checkDate->addDay();
                    continue;
                }

                if ($this->timeToMinutes($currentTime) > $this->timeToMinutes($dailyStart)) {
                    $dailyStart = $currentTime;
                }
            }

            $dailySlots = $doctor->getAvailableSlots(
                date: $dateString,
                dayStart: $dailyStart,
                dayEnd: $dayEnd,
                slotDuration: $duration
            );

            foreach ($dailySlots as $slot) {
                if (! $slot['is_available']) {
                    continue;
                }

                $slots[] = array_merge($slot, ['date' => $dateString]);

                if (count($slots) >= $limit) {
                    break 2;
                }
            }

            $checkDate->addDay();
        }

        return $slots;
    }

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

    private function timeToMinutes(string $time): int
    {
        Log::info("Converting time to minutes: $time");
        [$hours, $minutes] = explode(':', $time);

        return ((int) $hours * 60) + (int) $minutes;
    }

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
            'next_available_slots' => [],
            'message' => 'Doctor has no availability scheduled',
        ];
    }

    private function successResponse(
        Doctor $doctor,
        Service $service,
        int $serviceDuration,
        array $slots
    ): array {
        return [
            'id' => $doctor->id,
            'doctor_name' => $doctor->name,
            'service_id' => $service->id,
            'service_name' => $service->name,
            'service_duration_minutes' => $serviceDuration,
            'is_service_active' => $service->active,
            'next_available_slot' => $slots[0] ?? null,
            'next_available_slots' => $slots,
        ];
    }
}
