<?php

namespace App\Actions\Doctor;

use App\Models\Doctor;
use Illuminate\Support\Collection;

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

        // Create the frequency config with days and times only
        $frequencyConfig = [
            'days_of_week' => $normalizedDays,
            'start_time' => $startHour,
            'end_time' => $endHour,
        ];

        // Create the schedule
        $schedule = $doctor->schedules()->create([
            'name' => $metadata['name'] ?? 'Availability Rule',
            'description' => 'Regular availability schedule',
            'schedule_type' => 'availability',
            'start_date' => $effectiveFrom,
            'end_date' => $effectiveTo,
            'is_recurring' => true,
            'frequency' => 'weekly',
            'frequency_config' => $frequencyConfig,
            'metadata' => [],
            'is_active' => $metadata['is_active'] ?? true,
        ]);

        return $schedule;
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
