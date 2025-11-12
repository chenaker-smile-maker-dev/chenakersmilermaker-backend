<?php

namespace App\Filament\Admin\Resources\Doctors\Tables;

use App\Filament\Admin\Resources\Doctors\Actions\TableActions\HeaderActions;
use App\Filament\Admin\Resources\Doctors\Actions\TableActions\RecordActions;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Tables\Columns\IconColumn;
use Illuminate\Database\Eloquent\Builder;

class SchedulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->grow(false)
                    ->label('Schedule Name')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—')
                    ->weight('bold'),

                TextColumn::make('schedule_type')
                    ->label('Type')
                    ->toggleable()
                    ->formatStateUsing(function ($state, $record) {
                        $scheduleType = $record->schedule_type?->value ?? 'custom';
                        return match ($scheduleType) {
                            'availability' => 'Availability',
                            'blocked' => 'Blocked',
                            'appointment' => 'Appointment',
                            'custom' => 'Custom',
                            default => ucfirst($scheduleType)
                        };
                    })
                    ->badge()
                    ->color(
                        fn($state, $record) => match ($record->schedule_type?->value ?? 'custom') {
                            'availability' => 'success',
                            'blocked' => 'danger',
                            'appointment' => 'success',
                            default => 'gray'
                        }
                    ),

                TextColumn::make('start_date')
                    ->toggleable()
                    ->label('Date Range')
                    ->formatStateUsing(function ($state, $record) {
                        $from = $record->start_date->format('d/m/Y');
                        $to = $record->end_date ? $record->end_date->format('d/m/Y') : '—';
                        return "{$from} → {$to}";
                    })
                    ->sortable(),

                TextColumn::make('frequency')
                    ->toggleable()
                    ->label('Time Range')
                    ->state(function ($record) {
                        // Get times from periods (Zap stores times in schedule_periods table)
                        $periods = $record->periods()->get();

                        if ($periods->isEmpty()) {
                            return '—';
                        }

                        // Get start and end times from the first period
                        $period = $periods->first();
                        $startTime = $period->start_time ?? null;
                        $endTime = $period->end_time ?? null;

                        if ($startTime && $endTime) {
                            return "{$startTime} → {$endTime}";
                        }

                        return '—';
                    }),


                IconColumn::make('is_active')
                    ->label('Status')
                    ->toggleable()
                    ->boolean(),

                TextColumn::make('description')
                    ->label('Description')
                    ->limit(40)
                    ->wrap()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                RecordActions::editAction(),
                RecordActions::deleteAction(),
            ])
            ->headerActions([
                HeaderActions::addAvailabilityRuleAction(),
                HeaderActions::addBlockTimeAction(),
            ])
            ->filters([
                SelectFilter::make('schedule_type')
                    ->label('Schedule Type')
                    ->options([
                        'availability' => 'Availability',
                        'blocked' => 'Blocked',
                        'appointment' => 'Appointment',
                    ])
                    ->placeholder('All types'),

                Filter::make('is_active')
                    ->label('Active Only')
                    ->query(fn(Builder $query) => $query->where('is_active', true))
                    ->toggle(),

                Filter::make('upcoming')
                    ->label('Upcoming')
                    ->query(fn(Builder $query) => $query->where('start_date', '>=', now()->toDateString()))
                    ->toggle(),
            ]);
    }
}
