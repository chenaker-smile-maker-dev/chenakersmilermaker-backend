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
            Stat::make('Pending Urgent Bookings', $pending)
                ->description('Require immediate attention')
                ->color($pending > 0 ? 'danger' : 'success')
                ->icon('heroicon-o-exclamation-triangle'),
        ];
    }
}
