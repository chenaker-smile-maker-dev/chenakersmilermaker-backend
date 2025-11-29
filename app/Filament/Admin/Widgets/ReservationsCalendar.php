<?php

namespace App\Filament\Admin\Widgets;

use Guava\Calendar\Enums\CalendarViewType;
use Guava\Calendar\Filament\CalendarWidget;
use Guava\Calendar\ValueObjects\FetchInfo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ReservationsCalendar extends CalendarWidget
{
    // protected CalendarViewType $calendarView = CalendarViewType::DayGridMonth;
    protected CalendarViewType $calendarView = CalendarViewType::DayGridMonth;

    protected ?string $locale = 'en';

    protected function getEvents(FetchInfo $info): Collection|array|Builder
    {
        return \App\Models\Appointment::query()
            ->whereDate('from', '>=', $info->start)
            ->whereDate('to', '<=', $info->end);
    }
}
