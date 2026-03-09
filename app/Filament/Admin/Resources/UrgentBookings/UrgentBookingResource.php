<?php

namespace App\Filament\Admin\Resources\UrgentBookings;

use App\Enums\PatientNotificationType;
use App\Enums\UrgentBookingStatus;
use App\Filament\Admin\AdminNavigation;
use App\Filament\Admin\Resources\UrgentBookings\Pages\ListUrgentBookings;
use App\Filament\Admin\Resources\UrgentBookings\Pages\ViewUrgentBooking;
use App\Models\Doctor;
use App\Models\UrgentBooking;
use App\Services\PatientNotificationService;
use App\Services\PatientNotificationTemplates;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UrgentBookingResource extends Resource
{
    protected static ?string $model = UrgentBooking::class;

    protected static string|\BackedEnum|null $navigationIcon = AdminNavigation::URGENT_BOOKINGS_RESOURCE['icon'];
    protected static ?int $navigationSort = AdminNavigation::URGENT_BOOKINGS_RESOURCE['sort'];
    protected static ?string $navigationLabel = 'Urgent Bookings';

    public static function getNavigationGroup(): ?string
    {
        return __(AdminNavigation::URGENT_BOOKINGS_RESOURCE['group']);
    }

    public static function getNavigationBadge(): ?string
    {
        $count = UrgentBooking::where('status', UrgentBookingStatus::PENDING)->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID')->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('patient_name')->label('Patient')->searchable(),
                TextColumn::make('patient_phone')->label('Phone')->searchable(),
                TextColumn::make('patient_email')->label('Email')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('reason')->label('Reason')->limit(60)->tooltip(fn($record) => $record->reason),
                TextColumn::make('status')->label('Status')->badge(),
                TextColumn::make('preferred_datetime')->label('Preferred Time')->dateTime('M d, Y H:i')->sortable(),
                TextColumn::make('scheduled_datetime')->label('Scheduled')->dateTime('M d, Y H:i')->placeholder('—'),
                TextColumn::make('assignedDoctor.display_name')->label('Assigned Doctor')->placeholder('—'),
                TextColumn::make('created_at')->label('Submitted')->dateTime('M d, Y H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordAction('view')
            ->actions([
                Action::make('accept')
                    ->label('Accept')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn(UrgentBooking $record) => $record->status === UrgentBookingStatus::PENDING)
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
                    ->action(function (UrgentBooking $record, array $data) {
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
                    }),

                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn(UrgentBooking $record) => $record->status === UrgentBookingStatus::PENDING)
                    ->form([
                        Textarea::make('admin_notes')
                            ->label('Reason for Rejection')
                            ->required()
                            ->rows(3),
                    ])
                    ->requiresConfirmation()
                    ->action(function (UrgentBooking $record, array $data) {
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
                    }),

                Action::make('complete')
                    ->label('Mark Complete')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('info')
                    ->visible(fn(UrgentBooking $record) => $record->status === UrgentBookingStatus::ACCEPTED)
                    ->requiresConfirmation()
                    ->action(function (UrgentBooking $record) {
                        $record->update(['status' => UrgentBookingStatus::COMPLETED]);
                        Notification::make()->title('Marked as completed.')->success()->send();
                    }),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Patient Information')
                ->columns(2)
                ->schema([
                    TextEntry::make('patient_name')->label('Name'),
                    TextEntry::make('patient_phone')->label('Phone'),
                    TextEntry::make('patient_email')->label('Email')->placeholder('—'),
                    TextEntry::make('status')->label('Status')->badge(),
                    TextEntry::make('reason')->label('Reason')->columnSpanFull(),
                    TextEntry::make('description')->label('Description')->placeholder('—')->columnSpanFull(),
                ]),
            Section::make('Booking Details')
                ->columns(2)
                ->schema([
                    TextEntry::make('preferred_datetime')->label('Preferred Date/Time')->dateTime('M d, Y H:i')->placeholder('Not specified'),
                    TextEntry::make('scheduled_datetime')->label('Scheduled Date/Time')->dateTime('M d, Y H:i')->placeholder('Not scheduled'),
                    TextEntry::make('assignedDoctor.display_name')->label('Assigned Doctor')->placeholder('None'),
                    TextEntry::make('admin_notes')->label('Admin Notes')->placeholder('—')->columnSpanFull(),
                    TextEntry::make('created_at')->label('Submitted At')->dateTime('M d, Y H:i'),
                ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUrgentBookings::route('/'),
            'view' => ViewUrgentBooking::route('/{record}'),
        ];
    }
}
