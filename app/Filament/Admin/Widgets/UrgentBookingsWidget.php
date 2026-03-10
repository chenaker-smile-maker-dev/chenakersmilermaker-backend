<?php

namespace App\Filament\Admin\Widgets;

use App\Enums\UrgentBookingStatus;
use App\Models\UrgentBooking;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UrgentBookingsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 1;

    protected function getStats(): array
    {
        $pending = UrgentBooking::where('status', UrgentBookingStatus::PENDING)->count();

        return [
            Stat::make(__('panels/admin/widgets/dashboard.pending_urgent_bookings'), $pending)
                ->description(__('panels/admin/widgets/dashboard.pending_urgent_bookings_desc'))
                ->color($pending > 0 ? 'danger' : 'success')
                ->icon('heroicon-o-exclamation-triangle'),
        ];
    }
}
