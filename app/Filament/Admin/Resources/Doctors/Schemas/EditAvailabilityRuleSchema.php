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
        // Get times from periods (Zap stores times in schedule_periods table)
        $periods = $record->periods()->get();
        $firstPeriod = $periods->first();

        // Get times from the first period
        $startTime = $firstPeriod?->start_time ?? app(PlatformSettings::class)->start_time;
        $endTime = $firstPeriod?->end_time ?? app(PlatformSettings::class)->end_time;

        // Get days from frequency_config (Zap stores as ['days' => ['monday', 'tuesday', ...]])
        $daysOfWeekValues = $record->frequency_config['days'] ?? [];

        // Convert day names to numeric values for checkbox list
        $dayMap = [
            'sunday' => 0,
            'monday' => 1,
            'tuesday' => 2,
            'wednesday' => 3,
            'thursday' => 4,
            'friday' => 5,
            'saturday' => 6,
        ];

        $daysOfWeekNumeric = [];
        foreach ($daysOfWeekValues as $day) {
            if (isset($dayMap[$day])) {
                $daysOfWeekNumeric[] = $dayMap[$day];
            }
        }

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
                ->default($daysOfWeekNumeric)
                ->required()
                ->columns(2),

            TimePicker::make('start_hour')
                ->label('Start Time')
                ->required()
                ->seconds(false)
                ->native(true)
                ->format('H:i')
                ->placeholder(app(PlatformSettings::class)->start_time)
                ->default($startTime),

            TimePicker::make('end_hour')
                ->label('End Time')
                ->required()
                ->seconds(false)
                ->native(true)
                ->format('H:i')
                ->placeholder(app(PlatformSettings::class)->end_time)
                ->default($endTime),

            DatePicker::make('effective_from')
                ->label('Effective From')
                ->required()
                ->native(false)
                ->minDate(today())
                ->default($record->start_date),

            DatePicker::make('effective_to')
                ->native(false)
                ->label('Effective To')
                ->nullable()
                ->minDate(fn($get) => $get('effective_from') ? $get('effective_from') : today())
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
