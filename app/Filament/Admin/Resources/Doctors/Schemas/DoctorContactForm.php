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

class DoctorContactForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
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
            ]);
    }
}
