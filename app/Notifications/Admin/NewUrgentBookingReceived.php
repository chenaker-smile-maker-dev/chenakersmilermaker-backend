<?php

namespace App\Notifications\Admin;

use App\Models\UrgentBooking;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Actions\Action;
use Illuminate\Notifications\Notification;

class NewUrgentBookingReceived extends Notification
{
    public function __construct(private UrgentBooking $urgentBooking) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return FilamentNotification::make()
            ->title('🚨 URGENT BOOKING')
            ->icon('heroicon-o-exclamation-triangle')
            ->iconColor('danger')
            ->body("URGENT: {$this->urgentBooking->patient_name} - {$this->urgentBooking->patient_phone} - Reason: {$this->urgentBooking->reason}")
            ->actions([
                Action::make('view')
                    ->label('View Urgent Booking')
                    ->url(route('filament.admin.resources.urgent-bookings.view', $this->urgentBooking->id))
                    ->markAsRead(),
            ])
            ->getDatabaseMessage();
    }
}
