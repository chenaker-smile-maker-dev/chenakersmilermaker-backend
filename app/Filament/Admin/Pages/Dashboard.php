<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?int $navigationSort = 0;

    public function getWidgets(): array
    {
        return [
            \App\Filament\Admin\Widgets\StatsOverviewWidget::class,
            \App\Filament\Admin\Widgets\PendingActionsWidget::class,
            \App\Filament\Admin\Widgets\UrgentBookingsWidget::class,
            \App\Filament\Admin\Widgets\TodayAppointmentsWidget::class,
            \App\Filament\Admin\Widgets\AppointmentCalendarWidget::class,
            \App\Filament\Admin\Widgets\RecentActivityWidget::class,
        ];
    }

    public function getColumns(): int|string|array
    {
        return [
            'default' => 1,
            'md' => 2,
            'xl' => 3,
        ];
    }
}
