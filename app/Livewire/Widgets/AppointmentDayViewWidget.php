<?php

namespace App\Livewire\Widgets;

use Guava\Calendar\Enums\CalendarViewType;
use Guava\Calendar\Filament\CalendarWidget;
use Guava\Calendar\ValueObjects\FetchInfo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class AppointmentDayViewWidget extends CalendarWidget
{
    protected string $view = 'panels.widgets.appointment-day-view';

    protected int|string|array $columnSpan = 'full';

    protected ?string $locale = 'en';

    protected CalendarViewType $calendarView = CalendarViewType::TimeGridDay;

    private const DOCTOR_COLORS = [
        '#E74C3C', '#3498DB', '#2ECC71', '#F39C12', '#9B59B6',
        '#1ABC9C', '#E91E63', '#00BCD4', '#FF5722', '#4CAF50',
    ];

    protected function getEvents(FetchInfo $info): Collection|array|Builder
    {
        return \App\Models\Appointment::query()
            ->with('doctor')
            ->whereDate('from', '>=', $info->start)
            ->whereDate('to', '<=', $info->end)
            ->get()
            ->map(fn($appointment) => $appointment->toCalendarEvent()->backgroundColor($this->getDoctorColor($appointment->doctor_id)))
            ->toArray();
    }

    private function getDoctorColor(?int $doctorId): string
    {
        if (!$doctorId) return '#34D399';
        $colorIndex = ($doctorId - 1) % count(self::DOCTOR_COLORS);
        return self::DOCTOR_COLORS[$colorIndex];
    }

    public function getDoctorColors(): array
    {
        return self::DOCTOR_COLORS;
    }
}
