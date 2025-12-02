<?php

namespace App\Filament\Admin\Resources\Services\Widgets;

use App\Enums\Service\ServiceAvailability;
use App\Models\Service;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class ServiceCards extends StatsOverviewWidget
{
    protected int | string | array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        Cache::forget('services-stats');
        $stats = Cache::remember('services-stats', 60 * 30, function () {
            return [
                'total' => Service::count(),
                'daytime' => Service::where('availability', ServiceAvailability::DAYTIME)->count(),
                'nighttime' => Service::where('availability', ServiceAvailability::NIGHTTIME)->count(),
                'both' => Service::where('availability', ServiceAvailability::BOTH)->count(),
            ];
        });

        return [
            Stat::make(__('panels/admin/resources/service.total_services'), $stats['total'])
                ->color('info')
                ->icon('heroicon-o-briefcase'),

            Stat::make(__('panels/admin/resources/service.daytime_services'), $stats['daytime'])
                ->color('warning')
                ->icon('heroicon-o-sun'),

            Stat::make(__('panels/admin/resources/service.nighttime_services'), $stats['nighttime'])
                ->color('danger')
                ->icon('heroicon-o-moon'),

            Stat::make(__('panels/admin/resources/service.both_times_services'), $stats['both'])
                ->color('success')
                ->icon('heroicon-o-clock'),
        ];
    }
}
