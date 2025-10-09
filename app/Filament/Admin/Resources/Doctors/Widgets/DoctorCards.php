<?php

namespace App\Filament\Admin\Resources\Doctors\Widgets;

use App\Models\Doctor;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class DoctorCards extends StatsOverviewWidget
{
    protected int | string | array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 2;
    }

    protected function getStats(): array
    {
        Cache::forget('doctors-stats');
        $stats = Cache::remember('doctors-stats', 60 * 30, function () {
            return [
                'total' => Doctor::count(),
                'trashed' => Doctor::onlyTrashed()->count(),
                'chart' => $this->getDoctorRegistrationTrend(),
            ];
        });
        return [
            Stat::make('Nombre total de médecins', $stats['total'])
                ->color('success')
                ->chart($stats['chart']),

            Stat::make('Médecins supprimés', $stats['trashed'])
                ->color('danger'),
        ];
    }

    protected function getDoctorRegistrationTrend(): array
    {
        // Get doctor  registrations for the last 7 days
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $count = Doctor::whereDate('created_at', $date)->count();
            $data[] = $count;
        }

        return $data;
    }
}
