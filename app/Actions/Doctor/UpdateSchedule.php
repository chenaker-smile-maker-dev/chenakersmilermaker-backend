<?php

namespace App\Actions\Doctor;

use Zap\Facades\Zap;

class UpdateSchedule
{
    /**
     * Update an existing schedule (either availability rule or block time).
     *
     * @param mixed $schedule The schedule to update
     * @param array $data The updated data
     * @return mixed The updated schedule
     */
    public function __invoke($schedule, array $data)
    {
        $doctor = $schedule->schedulable;
        $isBlockTime = $schedule->schedule_type->value === 'blocked';

        // Map field names from form to action parameters
        $startDate = $data['effective_from'] ?? $data['start_date'] ?? null;
        $endDate = $data['effective_to'] ?? $data['end_date'] ?? null;

        // Delete old schedule
        $schedule->delete();

        // Create new schedule based on type using Zap
        if ($isBlockTime) {
            (new AddBlockTime())(
                $doctor,
                $data['reason'] ?? 'Block Time',
                $startDate,
                $endDate,
                $data['description'] ?? null,
                $data['has_time_restriction'] ? ($data['block_start_time'] ?? null) : null,
                $data['has_time_restriction'] ? ($data['block_end_time'] ?? null) : null,
                ['is_active' => $data['is_active'] ?? true]
            );
        } else {
            (new AddAvailabilityRule())(
                $doctor,
                $data['days_of_week'] ?? [],
                $data['start_hour'] ?? '09:00',
                $data['end_hour'] ?? '17:00',
                $startDate,
                $endDate ?? null,
                array_merge($data, ['is_active' => $data['is_active'] ?? true])
            );
        }

        return true;
    }
}
