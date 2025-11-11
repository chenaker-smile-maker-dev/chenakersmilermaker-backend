<?php

namespace App\Filament\Admin\Resources\Doctors\Schemas;

use App\Settings\PlatformSettings;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TextArea;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;

class EditAvailabilityRuleSchema
{
    public static function get($record): array
    {
        return [
            TextInput::make('name')
                ->label('Rule Name')
                ->required()
                ->placeholder('e.g., Regular Hours')
                ->default($record->name),

            CheckboxList::make('days_of_week')
                ->label('Days of Week')
                ->options([
                    0 => 'Sunday',
                    1 => 'Monday',
                    2 => 'Tuesday',
                    3 => 'Wednesday',
                    4 => 'Thursday',
                    5 => 'Friday',
                    6 => 'Saturday',
                ])
                ->default($record->frequency_config['days_of_week'] ?? [])
                ->required()
                ->columns(2),

            TimePicker::make('start_hour')
                ->label('Start Time')
                ->required()
                ->seconds(false)
                ->native(true)
                ->format('H:i')
                ->placeholder(app(PlatformSettings::class)->start_time)
                ->default($record->frequency_config['start_time'] ?? app(PlatformSettings::class)->start_time),

            TimePicker::make('end_hour')
                ->label('End Time')
                ->required()
                ->seconds(false)
                ->native(true)
                ->format('H:i')
                ->placeholder(app(PlatformSettings::class)->end_time)
                ->default($record->frequency_config['end_time'] ?? app(PlatformSettings::class)->end_time),

            DatePicker::make('start_date')
                ->label('Effective From')
                ->required()
                ->default($record->start_date),

            DatePicker::make('end_date')
                ->label('Effective To')
                ->nullable()
                ->helperText('Leave empty for ongoing')
                ->default($record->end_date),

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
