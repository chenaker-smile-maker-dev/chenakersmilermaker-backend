<?php

namespace App\Notifications\Admin;

use App\Models\Testimonial;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Actions\Action;
use Illuminate\Notifications\Notification;

class NewTestimonialSubmitted extends Notification
{
    public function __construct(private Testimonial $testimonial) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $patientName = $this->testimonial->patient_name ?? $this->testimonial->patient?->full_name ?? 'Unknown';

        return FilamentNotification::make()
            ->title('New Testimonial Submitted')
            ->icon('heroicon-o-star')
            ->iconColor('warning')
            ->body("New testimonial from {$patientName} - Rating: {$this->testimonial->rating}/5")
            ->actions([
                Action::make('view')
                    ->label('View Testimonial')
                    ->url(route('filament.admin.resources.testimonials.view', $this->testimonial->id))
                    ->markAsRead(),
            ])
            ->getDatabaseMessage();
    }
}
