<?php

namespace App\Filament\Admin\Widgets;

use App\Enums\Appointment\AppointmentStatus;
use App\Enums\UrgentBookingStatus;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\UrgentBooking;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $totalPatients = Patient::count();
        $todayAppointments = Appointment::whereDate('from', today())->count();
        $monthAppointments = Appointment::whereMonth('from', now()->month)
            ->whereYear('from', now()->year)
            ->count();
        $pendingCount = Appointment::where('status', AppointmentStatus::PENDING)->count()
            + UrgentBooking::where('status', UrgentBookingStatus::PENDING)->count();

        return [
            Stat::make(__('panels/admin/widgets/dashboard.total_patients'), $totalPatients)
                ->description(__('panels/admin/widgets/dashboard.total_patients_desc'))
                ->color('primary')
                ->icon('heroicon-o-user-group'),

            Stat::make(__('panels/admin/widgets/dashboard.todays_appointments'), $todayAppointments)
                ->description(today()->format('l, M d'))
                ->color('info')
                ->icon('heroicon-o-calendar-days'),

            Stat::make(__('panels/admin/widgets/dashboard.this_month'), $monthAppointments)
                ->description(now()->format('F Y') . ' ' . __('panels/admin/widgets/dashboard.this_month_suffix'))
                ->color('success')
                ->icon('heroicon-o-chart-bar'),

            Stat::make(__('panels/admin/widgets/dashboard.pending_actions'), $pendingCount)
                ->description(__('panels/admin/widgets/dashboard.pending_actions_desc'))
                ->color($pendingCount > 0 ? 'warning' : 'success')
                ->icon('heroicon-o-exclamation-circle'),
        ];
    }
}
