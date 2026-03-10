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

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $pendingAppointments = Appointment::where('status', AppointmentStatus::PENDING)->count();
        $cancellationRequests = Appointment::where('change_request_status', ChangeRequestStatus::PENDING_CANCELLATION)->count();
        $rescheduleRequests = Appointment::where('change_request_status', ChangeRequestStatus::PENDING_RESCHEDULE)->count();

        return [
            Stat::make(__('panels/admin/widgets/dashboard.pending_appointments'), $pendingAppointments)
                ->description(__('panels/admin/widgets/dashboard.pending_appointments_desc'))
                ->color($pendingAppointments > 0 ? 'warning' : 'success')
                ->icon('heroicon-o-clock'),

            Stat::make(__('panels/admin/widgets/dashboard.cancellation_requests'), $cancellationRequests)
                ->description(__('panels/admin/widgets/dashboard.cancellation_requests_desc'))
                ->color($cancellationRequests > 0 ? 'danger' : 'success')
                ->icon('heroicon-o-x-circle'),

            Stat::make(__('panels/admin/widgets/dashboard.reschedule_requests'), $rescheduleRequests)
                ->description(__('panels/admin/widgets/dashboard.reschedule_requests_desc'))
                ->color($rescheduleRequests > 0 ? 'warning' : 'success')
                ->icon('heroicon-o-arrow-path'),
        ];
    }
}
