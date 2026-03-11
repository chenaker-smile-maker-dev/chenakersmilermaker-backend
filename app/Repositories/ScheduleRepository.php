<?php

namespace App\Repositories;

use App\Models\Doctor;
use Zap\Enums\ScheduleTypes;
use Zap\Facades\Zap;
use Zap\Models\Schedule;

/**
 * Repository for managing doctor availability and blocked schedules.
 *
 * Wraps the Zap package API so Filament pages never talk directly to Zap.
 */
class ScheduleRepository
{
    /**
     * Day-name ↔ integer maps.
     */
    private const DAY_TO_INT = [
        'sunday'    => 0,
        'monday'    => 1,
        'tuesday'   => 2,
        'wednesday' => 3,
        'thursday'  => 4,
        'friday'    => 5,
        'saturday'  => 6,
    ];

    private const INT_TO_DAY = [
        0 => 'sunday',
        1 => 'monday',
        2 => 'tuesday',
        3 => 'wednesday',
        4 => 'thursday',
        5 => 'friday',
        6 => 'saturday',
    ];

    // ─── Availability ─────────────────────────────────────────────────────────

    /**
     * Create a recurring weekly availability rule for a doctor.
     *
     * @param  array<int>    $daysOfWeek   0 = Sunday … 6 = Saturday
     * @param  string        $startTime    H:i
     * @param  string        $endTime      H:i
     * @param  string        $effectiveFrom  Y-m-d
     * @param  string|null   $effectiveTo    Y-m-d  (null = ongoing)
     */
    public function createAvailability(
        Doctor $doctor,
        string $name,
        array $daysOfWeek,
        string $startTime,
        string $endTime,
        string $effectiveFrom,
        ?string $effectiveTo = null,
        ?string $description = null,
        bool $isActive = true
    ): Schedule {
        $dayNames = $this->intsToDayNames($daysOfWeek);

        $builder = Zap::for($doctor)
            ->named($name)
            ->availability()
            ->from($effectiveFrom)
            ->addPeriod($startTime, $endTime)
            ->weekly($dayNames);

        if ($effectiveTo) {
            $builder->to($effectiveTo);
        }
        if ($description) {
            $builder->description($description);
        }
        if ($isActive) {
            $builder->active();
        }

        return $builder->save();
    }

    /**
     * Update an existing availability schedule (delete + recreate).
     */
    public function updateAvailability(
        Schedule $schedule,
        string $name,
        array $daysOfWeek,
        string $startTime,
        string $endTime,
        string $effectiveFrom,
        ?string $effectiveTo = null,
        ?string $description = null,
        bool $isActive = true
    ): Schedule {
        $doctor = $schedule->schedulable;
        $schedule->periods()->delete();
        $schedule->delete();

        return $this->createAvailability(
            $doctor,
            $name,
            $daysOfWeek,
            $startTime,
            $endTime,
            $effectiveFrom,
            $effectiveTo,
            $description,
            $isActive
        );
    }

    // ─── Blocked ──────────────────────────────────────────────────────────────

    /**
     * Create a blocked-time entry for a doctor.
     *
     * @param  string|null   $startTime  H:i  (null = all-day)
     * @param  string|null   $endTime    H:i  (null = all-day)
     */
    public function createBlocked(
        Doctor $doctor,
        string $reason,
        string $startDate,
        string $endDate,
        ?string $startTime = null,
        ?string $endTime = null,
        ?string $description = null,
        bool $isActive = true
    ): Schedule {
        $blockStart = $startTime ?? '00:00';
        $blockEnd   = $endTime   ?? '23:59';

        // Zap requires end_date to be strictly after start_date.
        // When blocking a single day, push end_date forward by 1 day internally.
        $endDateResolved = \Carbon\Carbon::parse($endDate)->lte(\Carbon\Carbon::parse($startDate))
            ? \Carbon\Carbon::parse($startDate)->addDay()->toDateString()
            : $endDate;

        $builder = Zap::for($doctor)
            ->named($reason)
            ->blocked()
            ->from($startDate)
            ->to($endDateResolved)
            ->addPeriod($blockStart, $blockEnd);

        if ($description) {
            $builder->description($description);
        }
        if ($isActive) {
            $builder->active();
        }

        return $builder->save();
    }

    /**
     * Update an existing blocked schedule (delete + recreate).
     */
    public function updateBlocked(
        Schedule $schedule,
        string $reason,
        string $startDate,
        string $endDate,
        ?string $startTime = null,
        ?string $endTime = null,
        ?string $description = null,
        bool $isActive = true
    ): Schedule {
        $doctor = $schedule->schedulable;
        $schedule->periods()->delete();
        $schedule->delete();

        return $this->createBlocked(
            $doctor,
            $reason,
            $startDate,
            $endDate,
            $startTime,
            $endTime,
            $description,
            $isActive
        );
    }

    // ─── Shared ───────────────────────────────────────────────────────────────

    public function delete(Schedule $schedule): void
    {
        $schedule->periods()->delete();
        $schedule->delete();
    }

    // ─── Query helpers ────────────────────────────────────────────────────────

    public function availabilityForDoctor(Doctor $doctor): \Illuminate\Database\Eloquent\Builder
    {
        return $doctor->schedules()
            ->with('periods')
            ->where('schedule_type', ScheduleTypes::AVAILABILITY->value)
            ->getQuery();
    }

    public function blockedForDoctor(Doctor $doctor): \Illuminate\Database\Eloquent\Builder
    {
        return $doctor->schedules()
            ->with('periods')
            ->where('schedule_type', ScheduleTypes::BLOCKED->value)
            ->getQuery();
    }

    // ─── Data extraction helpers ──────────────────────────────────────────────

    /**
     * Extract stored form data from an availability Schedule so it can pre-fill an edit form.
     */
    public function availabilityFormData(Schedule $schedule): array
    {
        $period = $schedule->periods()->first();

        return [
            'name'           => $schedule->name ?? '',
            'days_of_week'   => $this->dayNamesToInts($schedule->frequency_config->days ?? []),
            'start_time'     => $period?->start_time ?? '09:00',
            'end_time'       => $period?->end_time   ?? '17:00',
            'effective_from' => $schedule->start_date?->format('Y-m-d'),
            'effective_to'   => $schedule->end_date?->format('Y-m-d'),
            'description'    => $schedule->description,
            'is_active'      => $schedule->is_active,
        ];
    }

    /**
     * Extract stored form data from a blocked Schedule.
     */
    public function blockedFormData(Schedule $schedule): array
    {
        $period          = $schedule->periods()->first();
        $hasRestriction  = $period
            && ! ($period->start_time === '00:00' && $period->end_time === '23:59');

        return [
            'reason'               => $schedule->name ?? '',
            'start_date'           => $schedule->start_date?->format('Y-m-d'),
            'end_date'             => $schedule->end_date?->format('Y-m-d'),
            'has_time_restriction' => $hasRestriction,
            'block_start_time'     => $hasRestriction ? $period->start_time : null,
            'block_end_time'       => $hasRestriction ? $period->end_time   : null,
            'description'          => $schedule->description,
            'is_active'            => $schedule->is_active,
        ];
    }

    // ─── Internal ─────────────────────────────────────────────────────────────

    private function intsToDayNames(array $ints): array
    {
        return array_map(fn(int|string $d) => self::INT_TO_DAY[(int) $d] ?? 'monday', $ints);
    }

    private function dayNamesToInts(array $names): array
    {
        return array_values(array_filter(array_map(
            fn(string $n) => self::DAY_TO_INT[strtolower($n)] ?? null,
            $names
        ), fn($v) => $v !== null));
    }
}
