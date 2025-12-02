<?php

namespace App\Filament\Admin\Resources\Patients\Widgets;

use App\Enums\Patient\Gender;
use App\Models\Patient;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class PatientCards extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        Cache::forget('patients-stats');
        $stats = Cache::remember('patients-stats', 60 * 30, function () {
            return [
                'total' => Patient::count(),
                'male' => Patient::where('gender', Gender::MALE->value)->count(),
                'female' => Patient::where('gender', Gender::FEMALE->value)->count(),
                'trashed' => Patient::onlyTrashed()->count(),
                'chart' => $this->getPatientRegistrationTrend(),
            ];
        });

        return [
            Stat::make(__('panels/admin/resources/patient.total_patients'), $stats['total'])
                // ->description(__('panels/admin/resources/patient.all_registered_patients'))
                ->color('success')
                ->chart($stats['chart']),

            Stat::make(__('panels/admin/resources/patient.male_patients'), $stats['male'])
                // ->description(__('panels/admin/resources/patient.male_patients_desc'))
                ->color('primary'),

            Stat::make(__('panels/admin/resources/patient.female_patients'), $stats['female'])
                // ->description(__('panels/admin/resources/patient.female_patients_desc'))
                ->color('secondary'),

            Stat::make(__('panels/admin/resources/patient.deleted_patients'), $stats['trashed'])
                // ->description(__('panels/admin/resources/patient.deleted_patients_desc'))
                ->color('danger'),
        ];
    }

    protected function getPatientRegistrationTrend(): array
    {
        // Get patient registrations for the last 7 days
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $count = Patient::whereDate('created_at', $date)->count();
            $data[] = $count;
        }

        return $data;
    }
}
