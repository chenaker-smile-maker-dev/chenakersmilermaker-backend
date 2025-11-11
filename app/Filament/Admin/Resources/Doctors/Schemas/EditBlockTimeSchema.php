<?php

namespace App\Filament\Admin\Resources\Doctors\Schemas;

use App\Settings\PlatformSettings;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TextArea;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;

class EditBlockTimeSchema
{
    public static function get($record): array
    {
        $hasTimeRestriction = isset($record->frequency_config['start_time']) && isset($record->frequency_config['end_time']);

        return [
            TextInput::make('name')
                ->label('Reason')
                ->required()
                ->placeholder('e.g., Holiday, Meeting, Personal')
                ->default($record->name),

            DatePicker::make('start_date')
                ->label('From Date')
                ->required()
                ->default($record->start_date),

            DatePicker::make('end_date')
                ->label('To Date')
                ->required()
                ->default($record->end_date),

            Toggle::make('has_time_restriction')
                ->label('Block specific hours only?')
                ->reactive()
                ->default($hasTimeRestriction),

            TimePicker::make('block_start_time')
                ->label('Block Start Time')
                ->seconds(false)
                ->native(true)
                ->format('H:i')
                ->placeholder(app(PlatformSettings::class)->start_time)
                ->default($hasTimeRestriction ? $record->frequency_config['start_time'] : app(PlatformSettings::class)->start_time)
                ->visible(fn($get) => $get('has_time_restriction'))
                ->required(fn($get) => $get('has_time_restriction')),

            TimePicker::make('block_end_time')
                ->label('Block End Time')
                ->seconds(false)
                ->native(true)
                ->format('H:i')
                ->placeholder(app(PlatformSettings::class)->end_time)
                ->default($hasTimeRestriction ? $record->frequency_config['end_time'] : app(PlatformSettings::class)->end_time)
                ->visible(fn($get) => $get('has_time_restriction'))
                ->required(fn($get) => $get('has_time_restriction')),

            TextArea::make('description')
                ->label('Description')
                ->nullable()
                ->default($record->description),

            Toggle::make('is_active')
                ->label('Active')
                ->default($record->is_active),
        ];
    }
}
