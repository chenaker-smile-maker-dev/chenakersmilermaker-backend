<?php

namespace App\Filament\Admin\Resources\UrgentBookings\Pages;

use App\Enums\PatientNotificationType;
use App\Enums\UrgentBookingStatus;
use App\Filament\Admin\Resources\UrgentBookings\UrgentBookingResource;
use App\Models\Doctor;
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
                ->visible(fn() => $this->record->status === UrgentBookingStatus::PENDING)
                ->form([
                    DateTimePicker::make('scheduled_datetime')
                        ->label('Scheduled Date & Time')
                        ->required()
                        ->seconds(false)
                        ->native(false),
                    Select::make('assigned_doctor_id')
                        ->label('Assign Doctor')
                        ->options(Doctor::all()->pluck('display_name', 'id'))
                        ->searchable(),
                    Textarea::make('admin_notes')
                        ->label('Notes for Patient')
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    $record = $this->record;
                    $record->update([
                        'status' => UrgentBookingStatus::ACCEPTED,
                        'scheduled_datetime' => $data['scheduled_datetime'],
                        'assigned_doctor_id' => $data['assigned_doctor_id'] ?? null,
                        'admin_notes' => $data['admin_notes'] ?? null,
                    ]);

                    if ($record->patient_id && $record->patient) {
                        $doctorName = $record->assignedDoctor?->display_name ?? '';
                        $scheduledAt = \Carbon\Carbon::parse($data['scheduled_datetime'])->format('M d, Y H:i');
                        $templates = PatientNotificationTemplates::urgentBookingAccepted(
                            $scheduledAt,
                            $doctorName,
                            $data['admin_notes'] ?? ''
                        );
                        PatientNotificationService::send(
                            $record->patient,
                            PatientNotificationType::URGENT_BOOKING_ACCEPTED->value,
                            $templates['title'],
                            $templates['body'],
                        );
                    }

                    Notification::make()->title('Urgent booking accepted.')->success()->send();
                    $this->refreshFormData(['status', 'scheduled_datetime', 'assigned_doctor_id', 'admin_notes']);
                }),

            Action::make('reject')
                ->label('Reject')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn() => $this->record->status === UrgentBookingStatus::PENDING)
                ->form([
                    Textarea::make('admin_notes')
                        ->label('Reason for Rejection')
                        ->required()
                        ->rows(3),
                ])
                ->requiresConfirmation()
                ->action(function (array $data) {
                    $record = $this->record;
                    $record->update([
                        'status' => UrgentBookingStatus::REJECTED,
                        'admin_notes' => $data['admin_notes'],
                    ]);

                    if ($record->patient_id && $record->patient) {
                        $templates = PatientNotificationTemplates::urgentBookingRejected($data['admin_notes']);
                        PatientNotificationService::send(
                            $record->patient,
                            PatientNotificationType::URGENT_BOOKING_REJECTED->value,
                            $templates['title'],
                            $templates['body'],
                        );
                    }

                    Notification::make()->title('Urgent booking rejected.')->warning()->send();
                    $this->refreshFormData(['status', 'admin_notes']);
                }),

            Action::make('complete')
                ->label('Mark Complete')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('info')
                ->visible(fn() => $this->record->status === UrgentBookingStatus::ACCEPTED)
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update(['status' => UrgentBookingStatus::COMPLETED]);
                    Notification::make()->title('Marked as completed.')->success()->send();
                    $this->refreshFormData(['status']);
                }),
        ];
    }
}
