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
                                    ->required()
                                    ->placeholder('Enter service name'),
                            ]),
                        Section::make('')
                            ->columnSpanFull()
                            ->columns(2)
                            ->schema([
                                TextInput::make('price')
                                    ->required()
                                    ->numeric()
                                    ->suffix('DZD')
                                    ->inputMode('decimal')
                                    ->placeholder('0.00')
                                    ->minValue(0)
                                    ->step('1'),
                                TextInput::make('duration')
                                    ->required()
                                    ->numeric()
                                    ->suffix('minutes')
                                    ->inputMode('numeric')
                                    ->placeholder('30')
                                    ->minValue(1)
                                    ->step('1'),
                                Select::make('availability')
                                    ->options(ServiceAvailability::class)
                                    ->default(ServiceAvailability::BOTH->value)
                                    ->required()
                                    ->native(false),
                                Toggle::make('active')
                                    ->required()
                                    ->inline(false)
                                    ->onIcon('heroicon-m-check')
                                    ->offIcon('heroicon-m-x-mark')
                                    ->label('Active Status'),
                            ]),
                    ]),
                Grid::make()
                    ->columnSpan(2)
                    ->schema([
                        Section::make('')
                            ->columnSpanFull()
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('service_image')
                                    ->collection('image')
                                    ->image()
                                    ->imageEditor()
                                    ->columnSpanFull()
                                    ->label(''),
                            ]),
                    ]),
            ]);
    }
}
