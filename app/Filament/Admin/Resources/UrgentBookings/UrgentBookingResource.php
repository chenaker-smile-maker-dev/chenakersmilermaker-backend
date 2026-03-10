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
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class UrgentBookingResource extends Resource
{
    protected static ?string $model = UrgentBooking::class;

    protected static string|\BackedEnum|null $navigationIcon = AdminNavigation::URGENT_BOOKINGS_RESOURCE['icon'];
    protected static ?int $navigationSort = AdminNavigation::URGENT_BOOKINGS_RESOURCE['sort'];

    public static function getModelLabel(): string
    {
        return __('panels/admin/resources/urgent_booking.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('panels/admin/resources/urgent_booking.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __(AdminNavigation::URGENT_BOOKINGS_RESOURCE['group']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['patient_name', 'patient_phone', 'reason'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string|Htmlable
    {
        return $record->patient_name;
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            __('panels/admin/resources/urgent_booking.search_patient') => $record->patient_name,
            __('panels/admin/resources/urgent_booking.search_status') => $record->status?->name ?? '—',
        ];
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
                TextColumn::make('id')->label(__('panels/admin/resources/urgent_booking.id') ?? 'ID')->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('patient_name')->label(__('panels/admin/resources/urgent_booking.patient'))->searchable(),
                TextColumn::make('patient_phone')->label(__('panels/admin/resources/urgent_booking.phone'))->searchable(),
                TextColumn::make('patient_email')->label(__('panels/admin/resources/urgent_booking.email'))->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('reason')->label(__('panels/admin/resources/urgent_booking.reason'))->limit(60)->tooltip(fn($record) => $record->reason),
                TextColumn::make('status')->label(__('panels/admin/resources/urgent_booking.status'))->badge(),
                TextColumn::make('preferred_datetime')->label(__('panels/admin/resources/urgent_booking.preferred_time'))->dateTime('M d, Y H:i')->sortable(),
                TextColumn::make('scheduled_datetime')->label(__('panels/admin/resources/urgent_booking.scheduled'))->dateTime('M d, Y H:i')->placeholder('—'),
                TextColumn::make('assignedDoctor.display_name')->label(__('panels/admin/resources/urgent_booking.assigned_doctor'))->placeholder('—'),
                TextColumn::make('created_at')->label(__('panels/admin/resources/urgent_booking.submitted'))->dateTime('M d, Y H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordAction('view')
            ->actions([
                Action::make('accept')
                    ->label(__('panels/admin/resources/urgent_booking.accept'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn(UrgentBooking $record) => $record->status === UrgentBookingStatus::PENDING)
                    ->form([
                        DateTimePicker::make('scheduled_datetime')
                            ->label(__('panels/admin/resources/urgent_booking.scheduled_datetime_label'))
                            ->required()
                            ->seconds(false)
                            ->native(false),
                        Select::make('assigned_doctor_id')
                            ->label(__('panels/admin/resources/urgent_booking.assign_doctor'))
                            ->options(Doctor::all()->pluck('display_name', 'id'))
                            ->searchable(),
                        Textarea::make('admin_notes')
                            ->label(__('panels/admin/resources/urgent_booking.notes_for_patient'))
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

                        Notification::make()->title(__('panels/admin/resources/urgent_booking.accepted_notification'))->success()->send();
                    }),

                Action::make('reject')
                    ->label(__('panels/admin/resources/urgent_booking.reject'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn(UrgentBooking $record) => $record->status === UrgentBookingStatus::PENDING)
                    ->form([
                        Textarea::make('admin_notes')
                            ->label(__('panels/admin/resources/urgent_booking.rejection_reason'))
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

                        Notification::make()->title(__('panels/admin/resources/urgent_booking.rejected_notification'))->warning()->send();
                    }),

                Action::make('complete')
                    ->label(__('panels/admin/resources/urgent_booking.complete'))
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('info')
                    ->visible(fn(UrgentBooking $record) => $record->status === UrgentBookingStatus::ACCEPTED)
                    ->requiresConfirmation()
                    ->action(function (UrgentBooking $record) {
                        $record->update(['status' => UrgentBookingStatus::COMPLETED]);
                        Notification::make()->title(__('panels/admin/resources/urgent_booking.completed_notification'))->success()->send();
                    }),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('panels/admin/resources/urgent_booking.patient_information'))
                ->columns(2)
                ->schema([
                    TextEntry::make('patient_name')->label(__('panels/admin/resources/urgent_booking.name')),
                    TextEntry::make('patient_phone')->label(__('panels/admin/resources/urgent_booking.phone')),
                    TextEntry::make('patient_email')->label(__('panels/admin/resources/urgent_booking.email'))->placeholder('—'),
                    TextEntry::make('status')->label(__('panels/admin/resources/urgent_booking.status'))->badge(),
                    TextEntry::make('reason')->label(__('panels/admin/resources/urgent_booking.reason'))->columnSpanFull(),
                    TextEntry::make('description')->label(__('panels/admin/resources/urgent_booking.description'))->placeholder('—')->columnSpanFull(),
                ]),
            Section::make(__('panels/admin/resources/urgent_booking.booking_details'))
                ->columns(2)
                ->schema([
                    TextEntry::make('preferred_datetime')->label(__('panels/admin/resources/urgent_booking.preferred_datetime'))->dateTime('M d, Y H:i')->placeholder('Not specified'),
                    TextEntry::make('scheduled_datetime')->label(__('panels/admin/resources/urgent_booking.scheduled_datetime'))->dateTime('M d, Y H:i')->placeholder('Not scheduled'),
                    TextEntry::make('assignedDoctor.display_name')->label(__('panels/admin/resources/urgent_booking.assigned_doctor'))->placeholder('None'),
                    TextEntry::make('admin_notes')->label(__('panels/admin/resources/urgent_booking.admin_notes'))->placeholder('—')->columnSpanFull(),
                    TextEntry::make('created_at')->label(__('panels/admin/resources/urgent_booking.created_at'))->dateTime('M d, Y H:i'),
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
