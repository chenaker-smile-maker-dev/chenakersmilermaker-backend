<?php

namespace App\Filament\Admin\Resources\Doctors\Schemas;

use AbdulmajeedJamaan\FilamentTranslatableTabs\TranslatableTabs;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DoctorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(5)
            ->components([
                Grid::make()
                    ->columnSpan(3)
                    ->schema([
                        Section::make('')
                            ->columnSpanFull()
                            ->columns(1)
                            ->schema([
                                TranslatableTabs::make('translatable_name')
                                    ->schema([
                                        TextInput::make('name')
                                            ->required(),
                                        TextInput::make('specialty')
                                            ->required(),
                                    ]),
                                // TranslatableTabs::make("translatable_specialty")
                                //     ->schema([
                                //     ]),
                            ])
                    ]),
                Grid::make()
                    ->columnSpan(2)
                    ->schema([
                        Section::make("")
                            ->columnSpanFull()
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('doctor_photo')
                                    ->collection('doctor_photo')
                                    ->image()
                                    ->imageEditor()
                                    ->columnSpanFull()
                                    ->label("")
                            ])
                    ]),
            ]);
    }
}
