<?php

namespace App\Filament\Admin\Resources\Doctors\Schemas;

use App\Settings\PlatformSettings;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;

class CreateAvailabilityRuleSchema
{
    public static function get(): array
    {
        return [
            TextInput::make('name')
                ->label('Rule Name')
                ->required()
                ->placeholder('e.g., Regular Hours'),

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
                ->required()
                ->columns(2),

            TimePicker::make('start_hour')
                ->label('Start Time')
                ->required()
                ->placeholder(app(PlatformSettings::class)->start_time)
                ->seconds(false)
                ->default(app(PlatformSettings::class)->start_time)
                ->native(true)
                ->format('H:i'),

            TimePicker::make('end_hour')
                ->label('End Time')
                ->required()
                ->placeholder(app(PlatformSettings::class)->end_time)
                ->default(app(PlatformSettings::class)->end_time)
                ->seconds(false)
                ->native(true)
                ->format('H:i'),

            DatePicker::make('effective_from')
                ->label('Effective From')
                ->required()
                ->minDate(today()),

            DatePicker::make('effective_to')
                ->label('Effective To')
                ->nullable()
                ->minDate(today())
                ->helperText('Leave empty for ongoing availability'),

            Textarea::make('description')
                ->label('Description')
                ->nullable()
                ->helperText('Optional additional details'),

            Toggle::make('is_active')
                ->label('Active')
                ->default(true),
        ];
    }
}
