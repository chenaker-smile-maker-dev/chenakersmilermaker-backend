<?php

namespace App\Actions\Doctor;

use App\Models\Doctor;

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

        // Delete old schedule
        $schedule->delete();

        // Create new schedule based on type
        if ($isBlockTime) {
            (new AddBlockTime())(
                $doctor,
                $data['name'] ?? $schedule->name,
                $data['start_date'] ?? $schedule->start_date,
                $data['end_date'] ?? $schedule->end_date,
                $data['description'] ?? null,
                $data['has_time_restriction'] ? $data['block_start_time'] : null,
                $data['has_time_restriction'] ? $data['block_end_time'] : null,
                [
                    'is_active' => $data['is_active'] ?? true,
                ]
            );
        } else {
            (new AddAvailabilityRule())(
                $doctor,
                $data['days_of_week'] ?? [],
                $data['start_hour'] ?? '09:00',
                $data['end_hour'] ?? '17:00',
                $data['start_date'] ?? $schedule->start_date,
                $data['end_date'] ?? $schedule->end_date,
                array_merge($data, ['is_active' => $data['is_active'] ?? true])
            );
        }

        return true;
    }
}
