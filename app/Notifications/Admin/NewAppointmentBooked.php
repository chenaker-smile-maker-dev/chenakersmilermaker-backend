<?php

namespace App\Notifications\Admin;

use App\Models\Appointment;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Notifications\Actions\Action;
use Illuminate\Notifications\Notification;

class NewAppointmentBooked extends Notification
{
    public function __construct(private Appointment $appointment) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return FilamentNotification::make()
            ->title('New Appointment Booked')
            ->icon('heroicon-o-calendar')
            ->iconColor('success')
            ->body("Patient {$this->appointment->patient->full_name} booked with Dr. {$this->appointment->doctor->display_name} on {$this->appointment->from->format('M d, Y')} at {$this->appointment->from->format('H:i')}")
            ->actions([
                Action::make('view')
                    ->label('View Appointment')
                    ->url(route('filament.admin.resources.appointments.view', $this->appointment->id))
                    ->markAsRead(),
            ])
            ->getDatabaseMessage();
    }
}
