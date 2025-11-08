<?php

namespace App\Filament\Admin\Resources\Doctors;

use App\Filament\Admin\AdminNavigation;
use App\Filament\Admin\Resources\Doctors\Pages\CreateDoctor;
use App\Filament\Admin\Resources\Doctors\Pages\EditDoctor;
use App\Filament\Admin\Resources\Doctors\Pages\ListDoctors;
use App\Filament\Admin\Resources\Doctors\Pages\ViewDoctor;
use App\Filament\Admin\Resources\Doctors\Pages\ManageDoctorSchedules;
use App\Filament\Admin\Resources\Doctors\RelationManagers\AppointmentsRelationManager;
use App\Filament\Admin\Resources\Doctors\Schemas\DoctorForm;
use App\Filament\Admin\Resources\Doctors\Schemas\DoctorInfolist;
use App\Filament\Admin\Resources\Doctors\Tables\DoctorsTable;
use App\Models\Doctor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DoctorResource extends Resource
{
    protected static ?string $model = Doctor::class;

    public static function getNavigationGroup(): ?string
    {
        return  __(AdminNavigation::DOCTORS_RESOURCE['group']);
    }

    public static function getModelLabel(): string
    {
        return 'Médecin';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Médecins';
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    protected static string|BackedEnum|null $navigationIcon = AdminNavigation::DOCTORS_RESOURCE['icon'];
    protected static ?int $navigationSort = AdminNavigation::DOCTORS_RESOURCE['sort'];
    protected static ?string $recordTitleAttribute = 'display_name';
    public static function getGlobalSearchResultTitle(Model $record): string | Htmlable
    {
        return $record->display_name;
    }
    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'specialty'];
    }
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Specialty' => $record->specialty,
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return DoctorForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DoctorInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DoctorsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            AppointmentsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDoctors::route('/'),
            'create' => CreateDoctor::route('/create'),
            'view' => ViewDoctor::route('/{record}'),
            'edit' => EditDoctor::route('/{record}/edit'),
            'manage-schedules' => ManageDoctorSchedules::route('/{record}/schedules'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
