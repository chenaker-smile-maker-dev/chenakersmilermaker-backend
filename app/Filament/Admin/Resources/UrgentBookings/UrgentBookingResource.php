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
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
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

    public static function getNavigationGroup(): ?string
    {
        return __(AdminNavigation::URGENT_BOOKINGS_RESOURCE['group']);
    }

    public static function getModelLabel(): string
    {
        return 'Urgent Booking';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Urgent Bookings';
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', UrgentBookingStatus::PENDING->value)->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'danger';
    }

    protected static string|BackedEnum|null $navigationIcon = AdminNavigation::URGENT_BOOKINGS_RESOURCE['icon'];
    protected static ?int $navigationSort = AdminNavigation::URGENT_BOOKINGS_RESOURCE['sort'];

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Patient Information')
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    TextEntry::make('patient_name')->label('Name'),
                    TextEntry::make('patient_phone')->label('Phone'),
                    TextEntry::make('patient_email')->label('Email')->placeholder('-'),
                    TextEntry::make('status')->badge(),
                    TextEntry::make('reason')->columnSpanFull()->placeholder('-'),
                    TextEntry::make('description')->columnSpanFull()->placeholder('-'),
                    TextEntry::make('preferred_datetime')->dateTime()->placeholder('-'),
                    TextEntry::make('scheduled_datetime')->dateTime()->placeholder('-'),
                    TextEntry::make('assignedDoctor.display_name')->label('Assigned Doctor')->placeholder('Not assigned'),
                    TextEntry::make('admin_notes')->label('Admin Notes')->placeholder('-')->columnSpanFull(),
                    TextEntry::make('created_at')->dateTime(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
                TextColumn::make('patient_name')->label('Patient Name')->searchable(),
                TextColumn::make('patient_phone')->label('Phone')->searchable(),
                TextColumn::make('reason')->label('Reason')->limit(50)->wrap(),
                TextColumn::make('status')->label('Status')->badge()->sortable(),
                TextColumn::make('assignedDoctor.display_name')->label('Assigned Doctor')->placeholder('-'),
                TextColumn::make('preferred_datetime')->label('Preferred Time')->dateTime()->sortable(),
                TextColumn::make('scheduled_datetime')->label('Scheduled Time')->dateTime()->placeholder('-'),
                TextColumn::make('created_at')->label('Created At')->dateTime()->sortable(),
            ])
            ->filters([])
            ->recordActions([
                ViewAction::make(),

                Action::make('accept')
                    ->label('Accept')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (UrgentBooking $record) => $record->status === UrgentBookingStatus::PENDING)
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
                    ->action(function (UrgentBooking $record, array $data) {
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

                        Notification::make()->success()->title('Urgent Booking Accepted')->send();
                    }),

                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (UrgentBooking $record) => $record->status === UrgentBookingStatus::PENDING)
                    ->form([
                        Textarea::make('admin_notes')->label('Reason for rejection')->required(),
                    ])
                    ->action(function (UrgentBooking $record, array $data) {
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

                        Notification::make()->success()->title('Urgent Booking Rejected')->send();
                    }),

                Action::make('complete')
                    ->label('Complete')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('success')
                    ->visible(fn (UrgentBooking $record) => $record->status === UrgentBookingStatus::ACCEPTED)
                    ->requiresConfirmation()
                    ->action(function (UrgentBooking $record) {
                        $record->update(['status' => UrgentBookingStatus::COMPLETED]);
                        Notification::make()->success()->title('Urgent Booking Completed')->send();
                    }),
            ])
            ->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUrgentBookings::route('/'),
            'view' => ViewUrgentBooking::route('/{record}'),
        ];
    }
}
