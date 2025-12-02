<?php

namespace App\Filament\Admin\Resources\Services;

use App\Filament\Admin\AdminNavigation;
use App\Filament\Admin\Resources\Patients\RelationManagers\AppointmentsRelationManager;
use App\Filament\Admin\Resources\Services\Pages\CreateService;
use App\Filament\Admin\Resources\Services\Pages\EditService;
use App\Filament\Admin\Resources\Services\Pages\ListServices;
use App\Filament\Admin\Resources\Services\Pages\ViewService;
use App\Filament\Admin\Resources\Services\RelationManagers\DoctorsRelationManager;
use App\Filament\Admin\Resources\Services\Schemas\ServiceForm;
use App\Filament\Admin\Resources\Services\Schemas\ServiceInfolist;
use App\Filament\Admin\Resources\Services\Tables\ServicesTable;
use App\Models\Service;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Support\Htmlable;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    public static function getNavigationGroup(): ?string
    {
        return  __(AdminNavigation::SERVICES_RESOURCE['group']);
    }

    public static function getModelLabel(): string
    {
        return __("panels/admin/resources/service.singular");
    }

    public static function getPluralModelLabel(): string
    {
        return __("panels/admin/resources/service.plural");
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    protected static string|BackedEnum|null $navigationIcon = AdminNavigation::SERVICES_RESOURCE['icon'];
    protected static ?int $navigationSort = AdminNavigation::SERVICES_RESOURCE['sort'];
    protected static ?string $recordTitleAttribute = 'name';
    public static function getGlobalSearchResultTitle(Model $record): string | Htmlable
    {
        return $record->name;
    }
    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'availability'];
    }
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Price' => $record->price,
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return ServiceForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ServiceInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ServicesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            DoctorsRelationManager::class,
            AppointmentsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServices::route('/'),
            'create' => CreateService::route('/create'),
            'view' => ViewService::route('/{record}'),
            'edit' => EditService::route('/{record}/edit'),
        ];
    }
}
