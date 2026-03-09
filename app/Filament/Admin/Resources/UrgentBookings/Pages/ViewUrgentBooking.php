<?php

namespace App\Filament\Admin\Resources\UrgentBookings\Pages;

use App\Enums\PatientNotificationType;
use App\Enums\UrgentBookingStatus;
use App\Filament\Admin\Resources\UrgentBookings\UrgentBookingResource;
use App\Models\Doctor;
use App\Models\UrgentBooking;
use App\Services\PatientNotificationService;
use App\Services\PatientNotificationTemplates;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewUrgentBooking extends ViewRecord
{
    protected static string $resource = UrgentBookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('accept')
                ->label('Accept')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => $this->record->status === UrgentBookingStatus::PENDING)
                ->form([
                    Select::make('assigned_doctor_id')
                        ->label('Assign Doctor')
                        ->options(Doctor::pluck('name', 'id'))
                        ->required()
                        ->searchable(),
                    DateTimePicker::make('scheduled_datetime')
                        ->label('Scheduled Date & Time')
                        ->required(),
                    Textarea::make('admin_notes')
                        ->label('Notes for Patient')
                        ->placeholder('Instructions for the patient...'),
                ])
                ->action(function (array $data) {
                    $record = $this->record;
                    $record->update([
                        'status' => UrgentBookingStatus::ACCEPTED,
                        'assigned_doctor_id' => $data['assigned_doctor_id'],
                        'scheduled_datetime' => $data['scheduled_datetime'],
                        'admin_notes' => $data['admin_notes'] ?? null,
                    ]);

                    if ($record->patient) {
                        $doctor = Doctor::find($data['assigned_doctor_id']);
                        $scheduledAt = \Carbon\Carbon::parse($data['scheduled_datetime'])->format('M d, Y H:i');
                        $template = PatientNotificationTemplates::urgentBookingAccepted(
                            $scheduledAt,
                            $doctor->display_name,
                            $data['admin_notes'] ?? '',
                        );
                        PatientNotificationService::send(
                            $record->patient,
                            PatientNotificationType::URGENT_BOOKING_ACCEPTED->value,
                            $template['title'],
                            $template['body'],
                            ['urgent_booking_id' => $record->id],
                        );
                    }

                    $this->refreshFormData(['status', 'assigned_doctor_id', 'scheduled_datetime', 'admin_notes']);
                    Notification::make()->success()->title('Urgent Booking Accepted')->send();
                }),

            Action::make('reject')
                ->label('Reject')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => $this->record->status === UrgentBookingStatus::PENDING)
                ->form([
                    Textarea::make('admin_notes')->label('Reason for rejection')->required(),
                ])
                ->action(function (array $data) {
                    $record = $this->record;
                    $record->update([
                        'status' => UrgentBookingStatus::REJECTED,
                        'admin_notes' => $data['admin_notes'],
                    ]);

                    if ($record->patient) {
                        $template = PatientNotificationTemplates::urgentBookingRejected(
                            $data['admin_notes'],
                        );
                        PatientNotificationService::send(
                            $record->patient,
                            PatientNotificationType::URGENT_BOOKING_REJECTED->value,
                            $template['title'],
                            $template['body'],
                            ['urgent_booking_id' => $record->id],
                        );
                    }

                    $this->refreshFormData(['status', 'admin_notes']);
                    Notification::make()->success()->title('Urgent Booking Rejected')->send();
                }),

            Action::make('complete')
                ->label('Complete')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('success')
                ->visible(fn () => $this->record->status === UrgentBookingStatus::ACCEPTED)
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update(['status' => UrgentBookingStatus::COMPLETED]);
                    $this->refreshFormData(['status']);
                    Notification::make()->success()->title('Urgent Booking Completed')->send();
                }),
        ];
    }
}
