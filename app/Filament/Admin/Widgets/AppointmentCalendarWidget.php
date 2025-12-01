<?php

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\Widget;
use Filament\Widgets\Concerns\InteractsWithPageTable;

#[\Filament\Widgets\Concerns\InteractsWithPageTable\Title('Appointment Calendar')]
class AppointmentCalendarWidget extends Widget
{
    protected string $view = 'panels.widgets.appointment-calendar-wrapper';

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Appointment Calendar';

    /**
     * Doctor color palette - colors selected for good contrast with both white and black text
     */
    private const DOCTOR_COLORS = [
        '#E74C3C', // Red - good contrast
        '#3498DB', // Blue - good contrast
        '#2ECC71', // Green - good contrast
        '#F39C12', // Orange - good contrast
        '#9B59B6', // Purple - good contrast
        '#1ABC9C', // Turquoise - good contrast
        '#E91E63', // Pink - good contrast
        '#00BCD4', // Cyan - good contrast
        '#FF5722', // Deep Orange - good contrast
        '#4CAF50', // Light Green - good contrast
    ];

    public string $currentView = 'dayGridMonth';

    public function changeView(string $view): void
    {
        $this->currentView = $view;
    }

    public function getDoctorColors(): array
    {
        return self::DOCTOR_COLORS;
    }
}
