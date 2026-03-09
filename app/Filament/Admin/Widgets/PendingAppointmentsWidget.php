<?php

namespace App\Filament\Admin\Widgets;

use App\Enums\Appointment\AppointmentStatus;
use App\Models\Appointment;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PendingAppointmentsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        return [
            Stat::make(
                'Pending Appointments',
                Appointment::where('status', AppointmentStatus::PENDING->value)->count()
            )
                ->icon('heroicon-o-clock')
                ->color('warning'),

            Stat::make(
                'Cancellation Requests',
                Appointment::where('change_request_status', 'pending_cancellation')->count()
            )
                ->icon('heroicon-o-x-circle')
                ->color('danger'),

            Stat::make(
                'Reschedule Requests',
                Appointment::where('change_request_status', 'pending_reschedule')->count()
            )
                ->icon('heroicon-o-arrow-path')
                ->color('info'),
        ];
    }
}
