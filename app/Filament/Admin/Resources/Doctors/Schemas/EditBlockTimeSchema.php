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
        // Get periods from the schedule (Zap stores times in schedule_periods table)
        $periods = $record->periods()->get();
        $firstPeriod = $periods->first();

        // Check if this block has time restriction (if period exists and doesn't block entire day)
        $hasTimeRestriction = false;
        $blockStartTime = null;
        $blockEndTime = null;

        if ($firstPeriod) {
            // If period is not 00:00-23:59, it has time restriction
            if (!($firstPeriod->start_time === '00:00' && $firstPeriod->end_time === '23:59')) {
                $hasTimeRestriction = true;
                $blockStartTime = $firstPeriod->start_time;
                $blockEndTime = $firstPeriod->end_time;
            }
        }

        return [
            TextInput::make('reason')
                ->label('Reason')
                ->required()
                ->placeholder('e.g., Holiday, Meeting, Personal')
                ->default($record->name),

            DatePicker::make('start_date')
                ->label('From Date')
                ->required()
                ->native(false)
                ->minDate(today())
                ->default($record->start_date),

            DatePicker::make('end_date')
                ->label('To Date')
                ->required()
                ->minDate(fn($get) => $get('start_date') ? $get('start_date') : today())
                ->native(false)
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
                ->default($blockStartTime ?? app(PlatformSettings::class)->start_time)
                ->visible(fn($get) => $get('has_time_restriction'))
                ->required(fn($get) => $get('has_time_restriction')),

            TimePicker::make('block_end_time')
                ->label('Block End Time')
                ->seconds(false)
                ->native(true)
                ->format('H:i')
                ->placeholder(app(PlatformSettings::class)->end_time)
                ->default($blockEndTime ?? app(PlatformSettings::class)->end_time)
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
