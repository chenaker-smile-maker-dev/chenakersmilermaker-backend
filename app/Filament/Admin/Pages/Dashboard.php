<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Widgets\AppointmentCalendarWidget;
use App\Filament\Admin\Widgets\PendingActionsWidget;
use App\Filament\Admin\Widgets\StatsOverviewWidget;
use App\Filament\Admin\Widgets\TodayAppointmentsWidget;
use App\Filament\Admin\Widgets\UrgentBookingsWidget;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\FilamentInfoWidget;

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
        ];
    }

    public function getColumns(): int|array
    {
        return 4;
    }
}
