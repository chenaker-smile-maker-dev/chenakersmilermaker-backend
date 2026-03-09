<?php

namespace App\Filament\Admin\Resources\Appointments\Schemas;

use App\Enums\Appointment\ChangeRequestStatus;
use App\Models\Appointment;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AppointmentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Appointment Details')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('patient.full_name')
                            ->label('Patient')
                            ->placeholder('-'),
                        TextEntry::make('patient.phone')
                            ->label('Patient Phone')
                            ->placeholder('-'),
                        TextEntry::make('patient.email')
                            ->label('Patient Email')
                            ->placeholder('-'),
                        TextEntry::make('doctor.display_name')
                            ->label(__('panels/admin/resources/appointment.doctor'))
                            ->placeholder('-'),
                        TextEntry::make('service.name')
                            ->label(__('panels/admin/resources/appointment.service'))
                            ->placeholder('-'),
                        TextEntry::make('from')
                            ->label('Date')
                            ->date('M d, Y'),
                        TextEntry::make('from')
                            ->label('Time From')
                            ->time('H:i'),
                        TextEntry::make('to')
                            ->label('Time To')
                            ->time('H:i'),
                        TextEntry::make('price')
                            ->money('DZD'),
                        TextEntry::make('status')
                            ->badge(),
                        TextEntry::make('confirmed_at')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('confirmedBy.name')
                            ->label('Confirmed By')
                            ->placeholder('-'),
                        TextEntry::make('admin_notes')
                            ->label('Admin Notes')
                            ->placeholder('-')
                            ->columnSpanFull(),
                        TextEntry::make('created_at')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('deleted_at')
                            ->dateTime()
                            ->visible(fn(Appointment $record): bool => $record->trashed()),
                    ]),

                Section::make('Change Request')
                    ->columns(2)
                    ->columnSpanFull()
                    ->visible(fn(Appointment $record): bool => $record->change_request_status !== null)
                    ->schema([
                        TextEntry::make('change_request_status')
                            ->label('Request Type')
                            ->badge()
                            ->color(fn($state) => match ($state) {
                                'pending_cancellation' => 'danger',
                                'pending_reschedule' => 'warning',
                                'approved' => 'success',
                                'rejected' => 'gray',
                                default => null,
                            })
                            ->formatStateUsing(fn($state) => $state ? str_replace('_', ' ', ucfirst($state)) : '-'),
                        TextEntry::make('cancellation_reason')
                            ->label('Cancellation Reason')
                            ->placeholder('-')
                            ->visible(fn(Appointment $record): bool => $record->change_request_status === ChangeRequestStatus::PENDING_CANCELLATION->value),
                        TextEntry::make('reschedule_reason')
                            ->label('Reschedule Reason')
                            ->placeholder('-')
                            ->visible(fn(Appointment $record): bool => $record->change_request_status === ChangeRequestStatus::PENDING_RESCHEDULE->value),
                        TextEntry::make('original_from')
                            ->label('Original Date/Time From')
                            ->dateTime()
                            ->placeholder('-')
                            ->visible(fn(Appointment $record): bool => $record->change_request_status === ChangeRequestStatus::PENDING_RESCHEDULE->value),
                        TextEntry::make('original_to')
                            ->label('Original Date/Time To')
                            ->dateTime()
                            ->placeholder('-')
                            ->visible(fn(Appointment $record): bool => $record->change_request_status === ChangeRequestStatus::PENDING_RESCHEDULE->value),
                        TextEntry::make('requested_new_from')
                            ->label('Requested New Date/Time From')
                            ->dateTime()
                            ->placeholder('-')
                            ->visible(fn(Appointment $record): bool => $record->change_request_status === ChangeRequestStatus::PENDING_RESCHEDULE->value),
                        TextEntry::make('requested_new_to')
                            ->label('Requested New Date/Time To')
                            ->dateTime()
                            ->placeholder('-')
                            ->visible(fn(Appointment $record): bool => $record->change_request_status === ChangeRequestStatus::PENDING_RESCHEDULE->value),
                    ]),
            ]);
    }
}
