<?php

namespace App\Filament\Admin\Widgets;

use App\Enums\Appointment\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\UrgentBooking;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 0;

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $todayCount = Appointment::whereDate('from', today())->count();
        $todayConfirmed = Appointment::whereDate('from', today())->where('status', AppointmentStatus::CONFIRMED->value)->count();
        $monthCount = Appointment::whereMonth('from', now()->month)->whereYear('from', now()->year)->count();

        $pendingActions =
            Appointment::where('status', AppointmentStatus::PENDING->value)->count()
            + Appointment::whereIn('change_request_status', ['pending_cancellation', 'pending_reschedule'])->count()
            + UrgentBooking::where('status', 'pending')->count();

        return [
            Stat::make('Total Patients', Patient::count())
                ->icon('heroicon-o-users')
                ->color('primary')
                ->description('Registered patients')
                ->descriptionIcon('heroicon-m-user-group'),

            Stat::make("Today's Appointments", $todayCount)
                ->icon('heroicon-o-calendar')
                ->color('success')
                ->description($todayConfirmed . ' confirmed today'),

            Stat::make('This Month', $monthCount)
                ->icon('heroicon-o-chart-bar')
                ->color('info')
                ->description('Appointments this month'),

            Stat::make('Pending Actions', $pendingActions)
                ->icon('heroicon-o-exclamation-triangle')
                ->color('danger')
                ->description('Require your attention'),
        ];
    }
}
