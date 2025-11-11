<?php

namespace App\Actions\Doctor;

use App\Models\Doctor;

class AddBlockTime
{
    /**
     * Add a block time (unavailable period) for a doctor.
     *
     * @param Doctor $doctor
     * @param string $reason The reason for blocking this time (e.g., "Holiday", "Meeting", "Personal")
     * @param string $fromDate Start date in Y-m-d format
     * @param string $toDate End date in Y-m-d format
     * @param string|null $description Optional description
     * @param string|null $blockStartTime Optional start time (H:i format) - if provided, blocks only these hours
     * @param string|null $blockEndTime Optional end time (H:i format) - if provided, blocks only these hours
     * @param array $metadata Optional additional metadata
     *
     * @return mixed The created schedule
     */
    public function __invoke(
        Doctor $doctor,
        string $reason,
        string $fromDate,
        string $toDate,
        ?string $description = null,
        ?string $blockStartTime = null,
        ?string $blockEndTime = null,
        array $metadata = []
    ) {
        // Create a non-recurring schedule for the block time
        $frequencyConfig = null;

        // If specific hours are blocked, store start_time and end_time only
        if ($blockStartTime && $blockEndTime) {
            $frequencyConfig = [
                'start_time' => $blockStartTime,
                'end_time' => $blockEndTime,
            ];
        }

        $schedule = $doctor->schedules()->create([
            'name' => $reason,
            'description' => $description,
            'schedule_type' => 'blocked',
            'start_date' => $fromDate,
            'end_date' => $toDate,
            'is_recurring' => false,
            'frequency' => null,
            'frequency_config' => $frequencyConfig,
            'metadata' => [],
            'is_active' => $metadata['is_active'] ?? true,
        ]);

        return $schedule;
    }
}
