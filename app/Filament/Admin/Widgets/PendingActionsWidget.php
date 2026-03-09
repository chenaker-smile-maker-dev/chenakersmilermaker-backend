<?php

namespace App\Filament\Admin\Widgets;

use App\Enums\Appointment\AppointmentStatus;
use App\Enums\Appointment\ChangeRequestStatus;
use App\Models\Appointment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PendingActionsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $pendingAppointments = Appointment::where('status', AppointmentStatus::PENDING)->count();
        $cancellationRequests = Appointment::where('change_request_status', ChangeRequestStatus::PENDING_CANCELLATION)->count();
        $rescheduleRequests = Appointment::where('change_request_status', ChangeRequestStatus::PENDING_RESCHEDULE)->count();

        return [
            Stat::make('Pending Appointments', $pendingAppointments)
                ->description('Awaiting confirmation')
                ->color($pendingAppointments > 0 ? 'warning' : 'success')
                ->icon('heroicon-o-clock'),

            Stat::make('Cancellation Requests', $cancellationRequests)
                ->description('Awaiting decision')
                ->color($cancellationRequests > 0 ? 'danger' : 'success')
                ->icon('heroicon-o-x-circle'),

            Stat::make('Reschedule Requests', $rescheduleRequests)
                ->description('Awaiting decision')
                ->color($rescheduleRequests > 0 ? 'warning' : 'success')
                ->icon('heroicon-o-arrow-path'),
        ];
    }
}
