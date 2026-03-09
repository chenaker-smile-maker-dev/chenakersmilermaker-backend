<?php

namespace App\Notifications\Admin;

use App\Models\Appointment;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Actions\Action;
use Illuminate\Notifications\Notification;

class AppointmentCancellationRequested extends Notification
{
    public function __construct(private Appointment $appointment) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return FilamentNotification::make()
            ->title('Cancellation Request')
            ->icon('heroicon-o-x-circle')
            ->iconColor('warning')
            ->body("Cancellation request: Appointment #{$this->appointment->id} - {$this->appointment->patient->full_name} - Reason: {$this->appointment->cancellation_reason}")
            ->actions([
                Action::make('view')
                    ->label('View Appointment')
                    ->url(route('filament.admin.resources.appointments.view', $this->appointment->id))
                    ->markAsRead(),
            ])
            ->getDatabaseMessage();
    }
}
