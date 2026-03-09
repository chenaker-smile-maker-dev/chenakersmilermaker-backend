<?php

namespace App\Filament\Admin\Widgets;

use App\Enums\UrgentBookingStatus;
use App\Models\UrgentBooking;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UrgentBookingsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make(
                'Pending Urgent Bookings',
                UrgentBooking::where('status', UrgentBookingStatus::PENDING->value)->count()
            )
                ->icon('heroicon-o-exclamation-triangle')
                ->color('danger')
                ->description('Pending urgent requests'),
        ];
    }
}
