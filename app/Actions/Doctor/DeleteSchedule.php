<?php

namespace App\Actions\Doctor;

class DeleteSchedule
{
    /**
     * Delete a schedule.
     *
     * @param mixed $schedule The schedule to delete
     * @return bool
     */
    public function __invoke($schedule): bool
    {
        return (bool) $schedule->delete();
    }
}
