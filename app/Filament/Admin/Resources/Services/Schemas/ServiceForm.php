<?php

namespace App\Filament\Admin\Resources\Services\Schemas;

use AbdulmajeedJamaan\FilamentTranslatableTabs\TranslatableTabs;
use App\Enums\Service\ServiceAvailability;
use Dom\Text;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(5)
            ->components([
                Grid::make()
                    ->columnSpan(3)
                    ->schema([
                        TranslatableTabs::make('translatable_name')
                            ->columnSpanFull()
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('panels/admin/resources/service.name'))
                                    ->required()
                                    ->placeholder(__('panels/admin/resources/service.enter_service_name')),
                            ]),
                        Section::make('')
                            ->columnSpanFull()
                            ->columns(2)
                            ->schema([
                                TextInput::make('price')
                                    ->label(__('panels/admin/resources/service.price'))
                                    ->required()
                                    ->numeric()
                                    ->suffix('DZD')
                                    ->inputMode('decimal')
                                    ->placeholder('0.00')
                                    ->minValue(0)
                                    ->step('1'),
                                TextInput::make('duration')
                                    ->label(__('panels/admin/resources/service.duration'))
                                    ->required()
                                    ->numeric()
                                    ->suffix(__('panels/admin/resources/service.minutes'))
                                    ->inputMode('numeric')
                                    ->placeholder('30')
                                    ->minValue(1)
                                    ->step('1'),
                                Select::make('availability')
                                    ->label(__('panels/admin/resources/service.availability'))
                                    ->options(ServiceAvailability::class)
                                    ->default(ServiceAvailability::BOTH->value)
                                    ->required()
                                    ->native(false),
                                Toggle::make('active')
                                    ->label(__('panels/admin/resources/service.active_status'))
                                    ->required()
                                    ->inline(false)
                                    ->onIcon('heroicon-m-check')
                                    ->offIcon('heroicon-m-x-mark'),
                            ]),
                    ]),
                Grid::make()
                    ->columnSpan(2)
                    ->schema([
                        Section::make('')
                            ->columnSpanFull()
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('service_image')
                                    ->label(__('panels/admin/resources/service.image'))
                                    ->collection('image')
                                    ->image()
                                    ->imageEditor()
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }
}
