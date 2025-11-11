<?php

namespace App\Filament\Admin\Resources\Doctors\Schemas;

use App\Settings\PlatformSettings;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;

class CreateBlockTimeSchema
{
    public static function get(): array
    {
        return [
            TextInput::make('reason')
                ->label('Reason')
                ->placeholder('e.g., Holiday, Meeting, Personal')
                ->required(),

            DatePicker::make('from_date')
                ->label('From Date')
                ->required(),

            DatePicker::make('to_date')
                ->label('To Date')
                ->required(),

            Toggle::make('block_specific_hours')
                ->label('Block specific hours only?')
                ->reactive()
                ->default(false),

            TimePicker::make('block_start_time')
                ->label('Block Start Time')
                ->placeholder(app(PlatformSettings::class)->start_time)
                ->seconds(false)
                ->default(app(PlatformSettings::class)->start_time)
                ->native(true)
                ->format('H:i')
                ->visible(fn($get) => $get('block_specific_hours'))
                ->required(fn($get) => $get('block_specific_hours')),

            TimePicker::make('block_end_time')
                ->label('Block End Time')
                ->placeholder(app(PlatformSettings::class)->end_time)
                ->seconds(false)
                ->default(app(PlatformSettings::class)->end_time)
                ->native(true)
                ->format('H:i')
                ->visible(fn($get) => $get('block_specific_hours'))
                ->required(fn($get) => $get('block_specific_hours')),

            TextArea::make('description')
                ->label('Description')
                ->nullable()
                ->helperText('Optional additional details'),

            Toggle::make('is_active')
                ->label('Active')
                ->default(true),
        ];
    }
}
