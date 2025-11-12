<?php

namespace App\Actions\Doctor;

use App\Models\Doctor;
use Zap\Facades\Zap;

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
        // Use Zap Facade to create blocked schedule with proper configuration
        $schedule = Zap::for($doctor)
            ->named($reason);

        if ($description) {
            $schedule->description($description);
        }

        $schedule
            ->blocked()
            ->from($fromDate)
            ->to($toDate);

        // If specific hours are blocked, add periods for those hours
        if ($blockStartTime && $blockEndTime) {
            $schedule->addPeriod($blockStartTime, $blockEndTime);
        } else {
            // Block the entire day (00:00 to 23:59 by default)
            $schedule->addPeriod('00:00', '23:59');
        }

        return $schedule->save();
    }
}
