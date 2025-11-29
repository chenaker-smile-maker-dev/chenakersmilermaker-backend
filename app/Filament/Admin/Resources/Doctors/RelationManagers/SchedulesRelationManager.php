<?php

namespace App\Filament\Admin\Resources\Doctors\RelationManagers;

use App\Filament\Admin\Resources\Doctors\Actions\TableActions\HeaderActions;
use App\Filament\Admin\Resources\Doctors\Actions\TableActions\RecordActions;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SchedulesRelationManager extends RelationManager
{
    protected static string $relationship = 'schedules';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->modifyQueryUsing(fn($query) =>  $query->with('periods'))
            ->modelLabel('Schedule')
            ->pluralModelLabel('Schedules')
            ->defaultSort('start_date', 'desc')
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
                        $from = $record->start_date->format('d-m-Y');
                        $to = $record->end_date ? $record->end_date->format('d-m-Y') : '—';
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
            ->filters([
                //
            ])
            ->headerActions([
                HeaderActions::addAvailabilityRuleAction(),
                HeaderActions::addBlockTimeAction(),
            ])
            ->recordActions([
                RecordActions::editAction(),
                RecordActions::deleteAction(),
                ViewAction::make(),
                // EditAction::make(),
                // DissociateAction::make(),
                // DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
