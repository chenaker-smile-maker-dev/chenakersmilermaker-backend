<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Widgets\AppointmentCalendarWidget;
use App\Filament\Admin\Widgets\PendingActionsWidget;
use App\Filament\Admin\Widgets\RecentActivityWidget;
use App\Filament\Admin\Widgets\StatsOverviewWidget;
use App\Filament\Admin\Widgets\TodayAppointmentsWidget;
use App\Filament\Admin\Widgets\UrgentBookingsWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        return [
            StatsOverviewWidget::class,
            PendingActionsWidget::class,
            UrgentBookingsWidget::class,
            TodayAppointmentsWidget::class,
            AppointmentCalendarWidget::class,
            RecentActivityWidget::class,
        ];
    }

    public function getColumns(): int|array
    {
        return [
            'default' => 1,
            'md' => 2,
            'xl' => 3,
        ];
    }
}
