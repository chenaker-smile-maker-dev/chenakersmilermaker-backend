<?php

namespace App\Filament\Admin\Resources\Doctors\Schemas;

use AbdulmajeedJamaan\FilamentTranslatableTabs\TranslatableTabs;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\KeyValue;
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
                        Section::make('Contact Information')
                            ->columnSpanFull()
                            ->columns(2)
                            ->description('Professional contact details')
                            ->schema([
                                TextInput::make('email')
                                    ->email()
                                    ->required()
                                    ->label('Email Address')
                                    ->placeholder('doctor@example.com'),
                                TextInput::make('phone')
                                    ->tel()
                                    ->required()
                                    ->label('Phone Number')
                                    ->placeholder('+213 665 65 65 65'),
                                Textarea::make('address')
                                    ->label('Address')
                                    ->placeholder('Enter clinic/office address')
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ]),
                        Section::make('Professional Qualifications')
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
                        Section::make('Professional Photo')
                            ->columnSpanFull()
                            ->description('Doctor profile picture')
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('doctor_photo')
                                    ->collection('doctor_photo')
                                    ->image()
                                    ->imageEditor()
                                    ->columnSpanFull()
                                    ->label('Upload Photo')
                                    ->helperText('Recommended: Square format, PNG or JPG')
                            ]),
                        Section::make('Additional Information')
                            ->columnSpanFull()
                            ->description('Professional metadata (key-value pairs)')
                            ->schema([
                                KeyValue::make('metadata')
                                    ->keyLabel('Key')
                                    ->valueLabel('Value')
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }
}
