<?php

namespace App\Filament\Admin\Resources\Services\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ServiceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(5)
            ->components([
                Section::make()
                    ->columns(2)
                    ->columnSpan(3)
                    ->schema([
                        TextEntry::make('price')
                            ->label(__('panels/admin/resources/service.price'))
                            ->money(),
                        IconEntry::make('active')
                            ->label(__('panels/admin/resources/service.active'))
                            ->boolean(),
                        TextEntry::make('availability')
                            ->label(__('panels/admin/resources/service.availability'))
                            ->badge(),
                            TextEntry::make('duration')
                                ->label(__('panels/admin/resources/service.duration'))
                                ->suffix(__('panels/admin/resources/service.minutes')),
                        TextEntry::make('created_at')
                            ->label(__('panels/admin/resources/service.created_at'))
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->label(__('panels/admin/resources/service.updated_at'))
                            ->dateTime()
                            ->placeholder('-'),
                    ]),
                Section::make()
                    ->columns(1)
                    ->columnSpan(2)
                    ->schema([
                            ImageEntry::make('image')
                                ->placeholder(__('panels/admin/resources/service.no_image_uploaded')),
                    ])
            ]);
    }
}
