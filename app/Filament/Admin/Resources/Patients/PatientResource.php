<?php

namespace App\Filament\Admin\Resources\Patients;

use App\Filament\Admin\AdminNavigation;
use App\Filament\Admin\Resources\Patients\Pages\CreatePatient;
use App\Filament\Admin\Resources\Patients\Pages\EditPatient;
use App\Filament\Admin\Resources\Patients\Pages\ListPatients;
use App\Filament\Admin\Resources\Patients\Pages\ViewPatient;
use App\Filament\Admin\Resources\Patients\Schemas\PatientForm;
use App\Filament\Admin\Resources\Patients\Schemas\PatientInfolist;
use App\Filament\Admin\Resources\Patients\Tables\PatientsTable;
use App\Models\Patient;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PatientResource extends Resource
{
    protected static ?string $model = Patient::class;
    public static function getNavigationGroup(): ?string
    {
        return  __(AdminNavigation::MANAGEMENT_GROUP);
    }

    public static function getModelLabel(): string
    {
        return 'Patient';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Patients';
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    protected static string|BackedEnum|null $navigationIcon = AdminNavigation::PATIENTS_RESOURCE['icon'];
    protected static ?int $navigationSort = AdminNavigation::PATIENTS_RESOURCE['sort'];
    protected static ?string $recordTitleAttribute = 'full_name';

    public static function form(Schema $schema): Schema
    {
        return PatientForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PatientInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PatientsTable::configure($table);
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
            'index' => ListPatients::route('/'),
            'create' => CreatePatient::route('/create'),
            'view' => ViewPatient::route('/{record}'),
            'edit' => EditPatient::route('/{record}/edit'),
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
