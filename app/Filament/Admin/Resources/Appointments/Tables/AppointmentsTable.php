<?php

namespace App\Filament\Admin\Resources\Appointments\Tables;

use App\Enums\Appointment\AppointmentStatus;
use App\Enums\Appointment\ChangeRequestStatus;
use App\Enums\PatientNotificationType;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Services\PatientNotificationService;
use App\Services\PatientNotificationTemplates;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AppointmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('patient.full_name')
                    ->label(__('panels/admin/resources/appointment.patient'))
                    ->searchable(),
                TextColumn::make('doctor.display_name')
                    ->label(__('panels/admin/resources/appointment.doctor'))
                    ->searchable(),
                TextColumn::make('service.name')
                    ->label(__('panels/admin/resources/appointment.service'))
                    ->searchable(),
                TextColumn::make('from')
                    ->label('Date')
                    ->date('M d, Y')
                    ->sortable(),
                TextColumn::make('time_range')
                    ->label('Time')
                    ->state(fn(Appointment $record) => $record->from->format('H:i') . ' - ' . $record->to->format('H:i')),
                TextColumn::make('status')
                    ->label(__('panels/admin/resources/appointment.status'))
                    ->badge(),
                TextColumn::make('change_request_status')
                    ->label('Change Request')
                    ->badge()
                    ->placeholder('-'),
                TextColumn::make('price')
                    ->label(__('panels/admin/resources/appointment.price'))
                    ->money('DZD')
                    ->badge()
                    ->alignCenter()
                    ->color('gray')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('panels/admin/resources/appointment.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('change_request_status')
                    ->options(\App\Enums\Appointment\ChangeRequestStatus::class)
                    ->label('Change Request'),
                SelectFilter::make('doctor_id')
                    ->relationship('doctor', 'name')
                    ->label('Doctor')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make(),

                Action::make('confirm')
                    ->label('Confirm')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn(Appointment $record) => $record->status === AppointmentStatus::PENDING)
                    ->requiresConfirmation()
                    ->action(function (Appointment $record) {
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

                        Notification::make()->success()->title('Appointment Confirmed')->send();
                    }),

                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn(Appointment $record) => $record->status === AppointmentStatus::PENDING)
                    ->form([
                        Textarea::make('admin_notes')
                            ->label('Reason for rejection')
                            ->required(),
                    ])
                    ->action(function (Appointment $record, array $data) {
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

                        Notification::make()->success()->title('Appointment Rejected')->send();
                    }),

                Action::make('complete')
                    ->label('Complete')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('success')
                    ->visible(fn(Appointment $record) => $record->status === AppointmentStatus::CONFIRMED)
                    ->requiresConfirmation()
                    ->action(function (Appointment $record) {
                        $record->update(['status' => AppointmentStatus::COMPLETED]);
                        Notification::make()->success()->title('Appointment Marked as Completed')->send();
                    }),

                Action::make('approve_cancellation')
                    ->label('Approve Cancellation')
                    ->icon('heroicon-o-check')
                    ->color('warning')
                    ->visible(fn(Appointment $record) => $record->change_request_status === ChangeRequestStatus::PENDING_CANCELLATION->value)
                    ->requiresConfirmation()
                    ->action(function (Appointment $record) {
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

                        Notification::make()->success()->title('Cancellation Approved')->send();
                    }),

                Action::make('reject_cancellation')
                    ->label('Reject Cancellation')
                    ->icon('heroicon-o-x-mark')
                    ->color('gray')
                    ->visible(fn(Appointment $record) => $record->change_request_status === ChangeRequestStatus::PENDING_CANCELLATION->value)
                    ->form([
                        Textarea::make('admin_notes')
                            ->label('Reason')
                            ->nullable(),
                    ])
                    ->action(function (Appointment $record, array $data) {
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

                        Notification::make()->success()->title('Cancellation Rejected')->send();
                    }),

                Action::make('approve_reschedule')
                    ->label('Approve Reschedule')
                    ->icon('heroicon-o-check')
                    ->color('info')
                    ->visible(fn(Appointment $record) => $record->change_request_status === ChangeRequestStatus::PENDING_RESCHEDULE->value)
                    ->requiresConfirmation()
                    ->action(function (Appointment $record) {
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

                        Notification::make()->success()->title('Reschedule Approved')->send();
                    }),

                Action::make('reject_reschedule')
                    ->label('Reject Reschedule')
                    ->icon('heroicon-o-x-mark')
                    ->color('gray')
                    ->visible(fn(Appointment $record) => $record->change_request_status === ChangeRequestStatus::PENDING_RESCHEDULE->value)
                    ->form([
                        Textarea::make('admin_notes')
                            ->label('Reason')
                            ->nullable(),
                    ])
                    ->action(function (Appointment $record, array $data) {
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

                        Notification::make()->success()->title('Reschedule Rejected')->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    \Filament\Actions\BulkAction::make('bulk_confirm')
                        ->label('Confirm Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                if ($record->status === AppointmentStatus::PENDING) {
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
                                }
                            }
                            Notification::make()->success()->title('Appointments Confirmed')->send();
                        }),
                    \Filament\Actions\BulkAction::make('bulk_reject')
                        ->label('Reject Selected')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                if ($record->status === AppointmentStatus::PENDING) {
                                    $record->update(['status' => AppointmentStatus::REJECTED]);
                                    $template = PatientNotificationTemplates::appointmentRejected(
                                        $record->doctor->display_name,
                                        $record->from->format('M d, Y'),
                                        $record->from->format('H:i'),
                                    );
                                    PatientNotificationService::send(
                                        $record->patient,
                                        PatientNotificationType::APPOINTMENT_REJECTED->value,
                                        $template['title'],
                                        $template['body'],
                                        ['appointment_id' => $record->id],
                                    );
                                }
                            }
                            Notification::make()->success()->title('Appointments Rejected')->send();
                        }),
                ]),
            ]);
    }
}
