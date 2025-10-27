<?php

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Artisan;
use Spatie\Health\ResultStores\ResultStore;

class HealthCheckStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $resultStore = app(ResultStore::class);
        $checkResults = $resultStore->latestResults();

        if (!$checkResults) {
            return [
                Stat::make('Status', 'No checks run yet')
                    ->description('Run a health check to see results')
                    ->color('gray'),
            ];
        }

        $storedResults = $checkResults->storedCheckResults;

        $totalChecks = count($storedResults);
        $okChecks = collect($storedResults)->where('status', 'ok')->count();
        $failedChecks = collect($storedResults)->where('status', 'failed')->count();
        $warningChecks = collect($storedResults)->where('status', 'warning')->count();

        $overallStatus = match (true) {
            $failedChecks > 0 => 'Failed',
            $warningChecks > 0 => 'Warning',
            default => 'Healthy',
        };

        $overallColor = match (true) {
            $failedChecks > 0 => 'danger',
            $warningChecks > 0 => 'warning',
            default => 'success',
        };

        return [
            Stat::make('Total Checks', $totalChecks)
                ->color($overallColor)
                ->icon($failedChecks > 0 ? 'heroicon-o-x-circle' : ($warningChecks > 0 ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-check-circle')),

            Stat::make('Passed', $okChecks)
                ->color('success')
                ->icon('heroicon-o-check-circle'),

            Stat::make('Failed', $failedChecks)
                ->color($failedChecks > 0 ? 'danger' : 'gray')
                ->icon($failedChecks > 0 ? 'heroicon-o-x-circle' : 'heroicon-o-check'),

            Stat::make('Warning', $warningChecks)
                ->color($warningChecks > 0 ? 'warning' : 'gray')
                ->icon($warningChecks > 0 ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-check'),
        ];
    }
}
