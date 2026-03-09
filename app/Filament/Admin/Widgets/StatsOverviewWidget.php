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
            Stat::make('Total Patients', $totalPatients)
                ->description('Registered patients')
                ->color('primary')
                ->icon('heroicon-o-user-group'),

            Stat::make("Today's Appointments", $todayAppointments)
                ->description(today()->format('l, M d'))
                ->color('info')
                ->icon('heroicon-o-calendar-days'),

            Stat::make('This Month', $monthAppointments)
                ->description(now()->format('F Y') . ' appointments')
                ->color('success')
                ->icon('heroicon-o-chart-bar'),

            Stat::make('Pending Actions', $pendingCount)
                ->description('Appointments + urgent bookings')
                ->color($pendingCount > 0 ? 'warning' : 'success')
                ->icon('heroicon-o-exclamation-circle'),
        ];
    }
}
