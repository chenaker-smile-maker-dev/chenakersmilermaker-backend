<?php

namespace App\Actions\Doctor;

use App\Models\Doctor;
use Zap\Facades\Zap;

class AddAvailabilityRule
{
    /**
     * Add an availability rule (recurring schedule) for a doctor.
     *
     * @param Doctor $doctor
     * @param array $daysOfWeek Array of day numbers (0=Sunday, 1=Monday, etc.) or day names
     * @param string $startHour Start time in H:i format (e.g., "09:00")
     * @param string $endHour End time in H:i format (e.g., "17:00")
     * @param string $effectiveFrom Start date in Y-m-d format
     * @param string $effectiveTo End date in Y-m-d format (nullable for ongoing)
     * @param array $metadata Optional additional metadata
     *
     * @return mixed The created schedule
     */
    public function __invoke(
        Doctor $doctor,
        array $daysOfWeek,
        string $startHour,
        string $endHour,
        string $effectiveFrom,
        ?string $effectiveTo = null,
        array $metadata = []
    ) {
        // Validate days of week (convert names to numbers if needed)
        $normalizedDays = $this->normalizeDaysOfWeek($daysOfWeek);

        // Use Zap Facade to create availability schedule
        $schedule = Zap::for($doctor)
            ->named($metadata['name'] ?? 'Availability Rule');

        // Add description if provided
        if (!empty($metadata['description'])) {
            $schedule->description($metadata['description']);
        }

        $schedule
            ->availability()
            ->from($effectiveFrom);

        if ($effectiveTo) {
            $schedule->to($effectiveTo);
        }

        // Add a single period for the working hours (Zap will apply it for the weekly recurrence)
        $schedule->addPeriod($startHour, $endHour);

        // Set weekly recurrence with specific days
        $dayNames = array_map(function ($day) {
            $dayMap = [0 => 'sunday', 1 => 'monday', 2 => 'tuesday', 3 => 'wednesday', 4 => 'thursday', 5 => 'friday', 6 => 'saturday'];
            return $dayMap[$day] ?? 'monday';
        }, $normalizedDays);

        $schedule->weekly($dayNames);

        // Set active status if provided
        if (isset($metadata['is_active'])) {
            if ($metadata['is_active']) {
                $schedule->active();
            }
        } else {
            $schedule->active(); // Default to active
        }

        return $schedule->save();
    }

    /**
     * Normalize days of week from various formats to numeric array
     *
     * @param array $daysOfWeek
     * @return array
     */
    private function normalizeDaysOfWeek(array $daysOfWeek): array
    {
        $dayMap = [
            'sunday' => 0,
            'monday' => 1,
            'tuesday' => 2,
            'wednesday' => 3,
            'thursday' => 4,
            'friday' => 5,
            'saturday' => 6,
        ];

        return array_map(function ($day) use ($dayMap) {
            if (is_numeric($day)) {
                return (int) $day;
            }
            return $dayMap[strtolower($day)] ?? $day;
        }, $daysOfWeek);
    }
}
