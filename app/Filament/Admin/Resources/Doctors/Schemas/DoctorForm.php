<?php

namespace App\Filament\Admin\Resources\Doctors\Schemas;

use AbdulmajeedJamaan\FilamentTranslatableTabs\TranslatableTabs;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DoctorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Section::make()
                // ->schema([
                TranslatableTabs::make('anyLabel')
                    ->schema([
                        TextInput::make('name')
                            ->required(),
                        TextInput::make('specialty')
                            ->required(),
                        TextInput::make('diplomas')
                            ->required(),
                    ])
                // ])->columnSpanFull(),
            ]);
    }
}
