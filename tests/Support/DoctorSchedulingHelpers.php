<?php

namespace Tests\Support;

use App\Actions\Doctor\AddAvailabilityRule;
use App\Actions\Doctor\AddBlockTime;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Service;
use Carbon\Carbon;

trait DoctorSchedulingHelpers
{
    /**
     * Create a doctor with an attached service.
     */
    protected function createDoctorWithService(array $doctorAttributes = [], array $serviceAttributes = []): array
    {
        $doctor = Doctor::factory()->create($doctorAttributes);
        $service = Service::factory()->create(array_merge([
            'active' => true,
            'duration' => 30,
            'price' => 1500,
        ], $serviceAttributes));

        $doctor->services()->attach($service);

        return ['doctor' => $doctor, 'service' => $service];
    }

    /**
     * Attach a weekly availability rule for the given doctor.
     */
    protected function addWeeklyAvailabilityForDoctor(
        Doctor $doctor,
        Carbon $startDate,
        array $days,
        string $fromTime,
        string $toTime,
        ?Carbon $endDate = null
    ): void
    {
        $end = $endDate ?? $startDate->copy()->addWeeks(2);

        (new AddAvailabilityRule())(
            $doctor,
            $days,
            $fromTime,
            $toTime,
            $startDate->toDateString(),
            $end->toDateString()
        );
    }

    /**
     * Block a specific day/time for a doctor.
     */
    protected function blockDoctorDuring(Doctor $doctor, Carbon $date, string $fromTime, string $toTime): void
    {
        (new AddBlockTime())(
            $doctor,
            'Test block',
            $date->toDateString(),
            $date->toDateString(),
            'Blocked for test',
            $fromTime,
            $toTime
        );
    }

    /**
     * Create a patient for booking flows.
     */
    protected function createPatient(): Patient
    {
        return Patient::factory()->create();
    }

    /**
     * Create a doctor without any services attached.
     */
    protected function createDoctor(array $attributes = []): Doctor
    {
        return Doctor::factory()->create($attributes);
    }

    /**
     * Create a service without attaching to a doctor.
     */
    protected function createService(array $attributes = []): Service
    {
        return Service::factory()->create(array_merge([
            'active' => true,
            'duration' => 30,
            'price' => 1500,
        ], $attributes));
    }
}
