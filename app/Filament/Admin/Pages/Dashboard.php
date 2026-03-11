<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Widgets\BookingCalendarWidget;
use App\Filament\Admin\Widgets\NewPatientsWidget;
use App\Filament\Admin\Widgets\StatsOverviewWidget;
use App\Filament\Admin\Widgets\TodayAppointmentsWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        return [
            // Row 1 – Stats overview (full width)
            StatsOverviewWidget::class,

            // Row 2 – Full-width booking calendar
            BookingCalendarWidget::class,

            // Row 3 – Today appointments (half) + New patients (half)
            TodayAppointmentsWidget::class,
            NewPatientsWidget::class,
        ];
    }

    public function getColumns(): int|array
    {
        return 4;
    }
}
