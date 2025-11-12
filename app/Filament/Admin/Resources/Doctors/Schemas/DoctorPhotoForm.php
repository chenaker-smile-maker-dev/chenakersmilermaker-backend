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

class DoctorPhotoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Doctor photo')
                    ->columnSpanFull()
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('doctor_photo')
                            ->hiddenLabel()
                            ->collection('doctor_photo')
                            ->image()
                            ->imageEditor()
                            ->columnSpanFull()
                            ->label('Upload Photo')
                            ->helperText('Recommended: Square format, PNG or JPG')
                    ]),
            ]);
    }
}
