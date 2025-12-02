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
                        Section::make(__('panels/admin/resources/doctor.basic_information'))
                            ->columnSpanFull()
                            ->columns(2)
                            ->schema([
                                TranslatableTabs::make('translatable_data')
                                    ->columnSpanFull()
                                    ->schema([
                                        TextInput::make('name')
                                            ->required()
                                            ->label(__('panels/admin/resources/doctor.doctor_name'))
                                            ->placeholder(__('panels/admin/resources/doctor.enter_doctor_name')),
                                        TextInput::make('specialty')
                                            ->required()
                                            ->label(__('panels/admin/resources/doctor.medical_specialty'))
                                            ->placeholder(__('panels/admin/resources/doctor.enter_specialty')),
                                    ]),
                            ]),
                        Section::make(__('panels/admin/resources/doctor.services'))
                            ->collapsed()
                            ->columnSpanFull()
                            ->schema([
                                Select::make('services')
                                    ->relationship('services', 'name')
                                    ->multiple()
                                    ->preload()
                                    ->label(__('panels/admin/resources/doctor.medical_services'))
                                    ->placeholder('Select services this doctor provides')
                                    ->columnSpanFull(),
                            ]),

                        Section::make(__('panels/admin/resources/doctor.professional_qualifications'))
                            ->collapsed()
                            ->columnSpanFull()
                            ->schema([
                                Repeater::make('diplomas')
                                    ->label(__('panels/admin/resources/doctor.diplomas_certifications'))
                                    ->simple(
                                        TextInput::make('value')
                                            ->label(__('panels/admin/resources/doctor.diploma_certification'))
                                            ->placeholder(__('panels/admin/resources/doctor.diploma_placeholder'))
                                            ->required(),
                                    )
                                    ->addActionLabel(__('panels/admin/resources/doctor.add_diploma'))
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
