<?php

namespace App\Filament\Admin\Resources\Appointments;

use App\Filament\Admin\AdminNavigation;
use App\Filament\Admin\Resources\Appointments\Pages\CreateAppointment;
use App\Filament\Admin\Resources\Appointments\Pages\EditAppointment;
use App\Filament\Admin\Resources\Appointments\Pages\ListAppointments;
use App\Filament\Admin\Resources\Appointments\Pages\ViewAppointment;
use App\Filament\Admin\Resources\Appointments\Schemas\AppointmentForm;
use App\Filament\Admin\Resources\Appointments\Schemas\AppointmentInfolist;
use App\Filament\Admin\Resources\Appointments\Tables\AppointmentsTable;
use App\Models\Appointment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;
    public static function getNavigationGroup(): ?string
    {
        return  __(AdminNavigation::APPOINTEMENT_RESOURCE['group']);
    }

    public static function getModelLabel(): string
    {
        return __("panels/admin/resources/appointment.singular");
    }

    public static function getPluralModelLabel(): string
    {
        return __("panels/admin/resources/appointment.plural");
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    protected static string|BackedEnum|null $navigationIcon = AdminNavigation::APPOINTEMENT_RESOURCE['icon'];
    protected static ?int $navigationSort = AdminNavigation::APPOINTEMENT_RESOURCE['sort'];

    public static function getGloballySearchableAttributes(): array
    {
        return ['patient.first_name', 'patient.last_name', 'doctor.name', 'service.name'];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['patient', 'doctor', 'service']);
    }

    public static function getGlobalSearchResultTitle(Model $record): string|Htmlable
    {
        $patient = $record->patient?->full_name ?? '—';
        $date = $record->from?->format('M d, Y H:i') ?? '—';

        return "{$patient} — {$date}";
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            __('panels/admin/resources/appointment.doctor') => $record->doctor?->display_name ?? '—',
            __('panels/admin/resources/appointment.service') => $record->service?->name ?? '—',
            __('panels/admin/resources/appointment.status') => $record->status?->name ?? '—',
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return AppointmentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AppointmentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AppointmentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAppointments::route('/'),
            'create' => CreateAppointment::route('/create'),
            'view' => ViewAppointment::route('/{record}'),
            'edit' => EditAppointment::route('/{record}/edit'),
        ];
    }
}
