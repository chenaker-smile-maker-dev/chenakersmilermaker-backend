<?php

namespace App\Filament\Admin\Resources\Events\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EventInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('panels/admin/resources/event.event_information'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('title')
                            ->label(__('panels/admin/resources/event.title'))
                            ->columnSpanFull()
                            ->size('lg'),
                        TextEntry::make('date')
                            ->label(__('panels/admin/resources/event.event_date'))
                            ->date('F j, Y')
                            ->placeholder('-'),
                        IconEntry::make('is_archived')
                            ->label(__('panels/admin/resources/event.status'))
                            ->boolean(),
                        TextEntry::make('location')
                            ->label(__('panels/admin/resources/event.location'))
                            ->columnSpanFull()
                            ->placeholder('-'),
                        TextEntry::make('description')
                            ->label(__('panels/admin/resources/event.description'))
                            ->columnSpanFull()
                            ->html()
                            ->placeholder('-'),
                    ]),
                Section::make(__('panels/admin/resources/event.system_information'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('created_at')
                            ->label(__('panels/admin/resources/event.created'))
                            ->dateTime('F j, Y H:i')
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->label(__('panels/admin/resources/event.last_updated'))
                            ->dateTime('F j, Y H:i')
                            ->placeholder('-'),
                    ]),
            ]);
    }
}
