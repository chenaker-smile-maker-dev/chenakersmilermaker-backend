<x-filament-panels::page>
    @livewire(
        \App\Filament\Admin\Resources\Doctors\Widgets\DoctorCalendarWidget::class,
        ['doctorId' => $this->record->id],
        key('doctor-calendar-' . $this->record->id)
    )
</x-filament-panels::page>
