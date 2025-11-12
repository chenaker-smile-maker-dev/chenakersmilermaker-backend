<?php

namespace App\Filament\Admin\Resources\Doctors\Schemas;

use AbdulmajeedJamaan\FilamentTranslatableTabs\TranslatableTabs;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
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
                    ->columnSpan(5)
                    ->schema([
                        Section::make('Basic Information')
                            ->columnSpanFull()
                            ->columns(2)
                            ->schema([
                                TranslatableTabs::make('translatable_data')
                                    ->columnSpanFull()
                                    ->schema([
                                        TextInput::make('name')
                                            ->required()
                                            ->label('Doctor Name')
                                            ->placeholder('Enter doctor name'),
                                        TextInput::make('specialty')
                                            ->required()
                                            ->label('Medical Specialty')
                                            ->placeholder('Enter specialty (e.g., Cardiology, Dermatology)'),
                                    ]),
                            ]),
                        Section::make('Services')
                            ->collapsed()
                            ->columnSpanFull()
                            ->schema([
                                Select::make('services')
                                    ->relationship('services', 'name')
                                    ->multiple()
                                    ->preload()
                                    ->label('Medical Services')
                                    ->placeholder('Select services this doctor provides')
                                    ->columnSpanFull(),
                            ]),

                        Section::make('Professional Qualifications')
                            ->collapsed()
                            ->columnSpanFull()
                            ->schema([
                                Repeater::make('diplomas')
                                    ->label('Diplomas & Certifications')
                                    ->simple(
                                        TextInput::make('value')
                                            ->label('Diploma/Certification')
                                            ->placeholder('e.g., MD from Harvard Medical School')
                                            ->required(),
                                    )
                                    ->addActionLabel('Add Diploma')
                                    ->defaultItems(1)
                                    ->columnSpanFull(),
                            ]),
                    ]),
                Grid::make()
                    ->columnSpan(2)
                    ->schema([

                        // Section::make('Additional Information')
                        //     ->columnSpanFull()
                        //     ->description('Professional metadata (key-value pairs)')
                        //     ->schema([
                        //         KeyValue::make('metadata')
                        //             ->keyLabel('Key')
                        //             ->valueLabel('Value')
                        //             ->columnSpanFull(),
                        //     ]),
                    ]),
            ]);
    }
}
