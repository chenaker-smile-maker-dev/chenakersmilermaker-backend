<?php

namespace App\Notifications\Admin;

use App\Models\Appointment;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Notifications\Actions\Action;
use Illuminate\Notifications\Notification;

class AppointmentRescheduleRequested extends Notification
{
    public function __construct(private Appointment $appointment) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $newDate = $this->appointment->requested_new_from?->format('M d, Y');
        $newTime = $this->appointment->requested_new_from?->format('H:i');

        return FilamentNotification::make()
            ->title('Reschedule Request')
            ->icon('heroicon-o-arrow-path')
            ->iconColor('info')
            ->body("Reschedule request: Appointment #{$this->appointment->id} - {$this->appointment->patient->full_name} wants to move to {$newDate} {$newTime}")
            ->actions([
                Action::make('view')
                    ->label('View Appointment')
                    ->url(route('filament.admin.resources.appointments.view', $this->appointment->id))
                    ->markAsRead(),
            ])
            ->getDatabaseMessage();
    }
}
