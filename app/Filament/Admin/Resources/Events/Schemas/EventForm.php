<?php

namespace App\Filament\Admin\Resources\Events\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use AbdulmajeedJamaan\FilamentTranslatableTabs\TranslatableTabs;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TranslatableTabs::make('translatable_data')
                ->columnSpanFull()
                ->schema([
                    TextInput::make('title')->required(),
                    TextInput::make('slug')->required(),
                    TextInput::make('description'),
                    DatePicker::make('date')->required(),
                    Toggle::make('is_archived')->required(),
                    TextInput::make('location')
                ]),
        ]);
    }
}
