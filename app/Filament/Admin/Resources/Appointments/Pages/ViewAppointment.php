<?php

namespace App\Filament\Admin\Resources\Appointments\Pages;

use App\Enums\Appointment\AppointmentStatus;
use App\Enums\Appointment\ChangeRequestStatus;
use App\Enums\PatientNotificationType;
use App\Filament\Admin\Resources\Appointments\AppointmentResource;
use App\Models\Appointment;
use App\Services\PatientNotificationService;
use App\Services\PatientNotificationTemplates;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewAppointment extends ViewRecord
{
    protected static string $resource = AppointmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),

            Action::make('confirm')
                ->label('Confirm')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn() => $this->record->status === AppointmentStatus::PENDING)
                ->requiresConfirmation()
                ->action(function () {
                    $record = $this->record;
                    $record->update([
                        'status' => AppointmentStatus::CONFIRMED,
                        'confirmed_by' => auth()->id(),
                        'confirmed_at' => now(),
                    ]);

                    $template = PatientNotificationTemplates::appointmentConfirmed(
                        $record->doctor->display_name,
                        $record->from->format('M d, Y'),
                        $record->from->format('H:i'),
                    );
                    PatientNotificationService::send(
                        $record->patient,
                        PatientNotificationType::APPOINTMENT_CONFIRMED->value,
                        $template['title'],
                        $template['body'],
                        ['appointment_id' => $record->id],
                    );

                    $this->refreshFormData(['status', 'confirmed_at', 'confirmed_by']);
                    Notification::make()->success()->title('Appointment Confirmed')->send();
                }),

            Action::make('reject')
                ->label('Reject')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn() => $this->record->status === AppointmentStatus::PENDING)
                ->form([
                    Textarea::make('admin_notes')
                        ->label('Reason for rejection')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $record = $this->record;
                    $record->update([
                        'status' => AppointmentStatus::REJECTED,
                        'admin_notes' => $data['admin_notes'],
                    ]);

                    $template = PatientNotificationTemplates::appointmentRejected(
                        $record->doctor->display_name,
                        $record->from->format('M d, Y'),
                        $record->from->format('H:i'),
                        $data['admin_notes'],
                    );
                    PatientNotificationService::send(
                        $record->patient,
                        PatientNotificationType::APPOINTMENT_REJECTED->value,
                        $template['title'],
                        $template['body'],
                        ['appointment_id' => $record->id],
                    );

                    $this->refreshFormData(['status', 'admin_notes']);
                    Notification::make()->success()->title('Appointment Rejected')->send();
                }),

            Action::make('complete')
                ->label('Complete')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('success')
                ->visible(fn() => $this->record->status === AppointmentStatus::CONFIRMED)
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update(['status' => AppointmentStatus::COMPLETED]);
                    $this->refreshFormData(['status']);
                    Notification::make()->success()->title('Appointment Completed')->send();
                }),

            Action::make('approve_cancellation')
                ->label('Approve Cancellation')
                ->icon('heroicon-o-check')
                ->color('warning')
                ->visible(fn() => $this->record->change_request_status === ChangeRequestStatus::PENDING_CANCELLATION->value)
                ->requiresConfirmation()
                ->action(function () {
                    $record = $this->record;
                    $record->update([
                        'status' => AppointmentStatus::CANCELLED,
                        'change_request_status' => ChangeRequestStatus::APPROVED->value,
                    ]);

                    $template = PatientNotificationTemplates::cancellationApproved(
                        $record->doctor->display_name,
                        $record->from->format('M d, Y'),
                    );
                    PatientNotificationService::send(
                        $record->patient,
                        PatientNotificationType::CANCELLATION_APPROVED->value,
                        $template['title'],
                        $template['body'],
                        ['appointment_id' => $record->id],
                    );

                    $this->refreshFormData(['status', 'change_request_status']);
                    Notification::make()->success()->title('Cancellation Approved')->send();
                }),

            Action::make('reject_cancellation')
                ->label('Reject Cancellation')
                ->icon('heroicon-o-x-mark')
                ->color('gray')
                ->visible(fn() => $this->record->change_request_status === ChangeRequestStatus::PENDING_CANCELLATION->value)
                ->form([
                    Textarea::make('admin_notes')->label('Reason')->nullable(),
                ])
                ->action(function (array $data) {
                    $record = $this->record;
                    $record->update([
                        'change_request_status' => ChangeRequestStatus::REJECTED->value,
                        'admin_notes' => $data['admin_notes'] ?? $record->admin_notes,
                    ]);

                    $template = PatientNotificationTemplates::cancellationRejected(
                        $record->doctor->display_name,
                        $record->from->format('M d, Y'),
                    );
                    PatientNotificationService::send(
                        $record->patient,
                        PatientNotificationType::CANCELLATION_REJECTED->value,
                        $template['title'],
                        $template['body'],
                        ['appointment_id' => $record->id],
                    );

                    $this->refreshFormData(['change_request_status', 'admin_notes']);
                    Notification::make()->success()->title('Cancellation Rejected')->send();
                }),

            Action::make('approve_reschedule')
                ->label('Approve Reschedule')
                ->icon('heroicon-o-check')
                ->color('info')
                ->visible(fn() => $this->record->change_request_status === ChangeRequestStatus::PENDING_RESCHEDULE->value)
                ->requiresConfirmation()
                ->action(function () {
                    $record = $this->record;
                    $record->update([
                        'from' => $record->requested_new_from,
                        'to' => $record->requested_new_to,
                        'change_request_status' => ChangeRequestStatus::APPROVED->value,
                    ]);

                    $template = PatientNotificationTemplates::rescheduleApproved(
                        $record->doctor->display_name,
                        $record->requested_new_from->format('M d, Y'),
                        $record->requested_new_from->format('H:i'),
                    );
                    PatientNotificationService::send(
                        $record->patient,
                        PatientNotificationType::RESCHEDULE_APPROVED->value,
                        $template['title'],
                        $template['body'],
                        ['appointment_id' => $record->id],
                    );

                    $this->refreshFormData(['from', 'to', 'change_request_status']);
                    Notification::make()->success()->title('Reschedule Approved')->send();
                }),

            Action::make('reject_reschedule')
                ->label('Reject Reschedule')
                ->icon('heroicon-o-x-mark')
                ->color('gray')
                ->visible(fn() => $this->record->change_request_status === ChangeRequestStatus::PENDING_RESCHEDULE->value)
                ->form([
                    Textarea::make('admin_notes')->label('Reason')->nullable(),
                ])
                ->action(function (array $data) {
                    $record = $this->record;
                    $record->update([
                        'change_request_status' => ChangeRequestStatus::REJECTED->value,
                        'admin_notes' => $data['admin_notes'] ?? $record->admin_notes,
                    ]);

                    $template = PatientNotificationTemplates::rescheduleRejected(
                        $record->doctor->display_name,
                        $record->from->format('M d, Y'),
                        $record->from->format('H:i'),
                    );
                    PatientNotificationService::send(
                        $record->patient,
                        PatientNotificationType::RESCHEDULE_REJECTED->value,
                        $template['title'],
                        $template['body'],
                        ['appointment_id' => $record->id],
                    );

                    $this->refreshFormData(['change_request_status', 'admin_notes']);
                    Notification::make()->success()->title('Reschedule Rejected')->send();
                }),
        ];
    }
}
