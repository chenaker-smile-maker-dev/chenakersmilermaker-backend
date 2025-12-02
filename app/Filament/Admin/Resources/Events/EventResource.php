<?php

namespace App\Filament\Admin\Resources\Events;

use App\Filament\Admin\Resources\Events\Pages\CreateEvent;
use App\Filament\Admin\Resources\Events\Pages\EditEvent;
use App\Filament\Admin\Resources\Events\Pages\ListEvents;
use App\Filament\Admin\Resources\Events\Pages\ViewEvent;
use App\Filament\Admin\Resources\Events\Schemas\EventForm;
use App\Filament\Admin\Resources\Events\Schemas\EventInfolist;
use App\Filament\Admin\Resources\Events\Tables\EventsTable;
use App\Models\Event;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Admin\AdminNavigation;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;
    public static function getNavigationGroup(): ?string
    {
        return  __(AdminNavigation::EVENTS_RESOURCE['group']);
    }

    public static function getModelLabel(): string
    {
        return __("panels/admin/resources/event.singular");
    }

    public static function getPluralModelLabel(): string
    {
        return __("panels/admin/resources/event.plural");
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    protected static string|BackedEnum|null $navigationIcon = AdminNavigation::EVENTS_RESOURCE['icon'];
    protected static ?int $navigationSort = AdminNavigation::EVENTS_RESOURCE['sort'];

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return EventForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return EventInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EventsTable::configure($table);
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
            'index' => ListEvents::route('/'),
            'create' => CreateEvent::route('/create'),
            'view' => ViewEvent::route('/{record}'),
            'edit' => EditEvent::route('/{record}/edit'),
        ];
    }
}
