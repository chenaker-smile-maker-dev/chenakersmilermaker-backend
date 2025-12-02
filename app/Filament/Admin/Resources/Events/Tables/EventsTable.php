<?php

namespace App\Filament\Admin\Resources\Events\Tables;

use App\Filament\Exports\EventExporter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label(__('panels/admin/resources/event.title'))
                    ->searchable()
                    ->description(fn($record) => $record->getTranslation('location', app()->getLocale()) ?? __('panels/admin/resources/event.no_location'))
                    ->limit(50)
                    ->toggleable(),

                TextColumn::make('date')
                    ->label(__('panels/admin/resources/event.event_date'))
                    ->date('M d, Y')
                    ->sortable()
                    ->icon('heroicon-o-calendar-days')
                    ->toggleable(),

                TextColumn::make('description')
                    ->label(__('panels/admin/resources/event.description'))
                    ->searchable()
                    ->limit(75)
                    ->tooltip(fn($record) => $record->getTranslation('description', app()->getLocale()))
                    ->placeholder('-')
                    ->toggleable(),

                IconColumn::make('is_archived')
                    ->label(__('panels/admin/resources/event.archived'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->tooltip(fn($record) => $record->is_archived ? __('panels/admin/resources/event.archived') : __('panels/admin/resources/event.not_archived'))
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label(__('panels/admin/resources/event.created'))
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label(__('panels/admin/resources/event.updated'))
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('deleted_at')
                    ->label(__('panels/admin/resources/event.deleted'))
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->placeholder(__('panels/admin/resources/event.not_deleted'))
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),

                TernaryFilter::make('is_archived')
                    ->label(__('panels/admin/resources/event.archived_status'))
                    ->placeholder(__('panels/admin/resources/event.all_events'))
                    ->trueLabel(__('panels/admin/resources/event.archived_only'))
                    ->falseLabel(__('panels/admin/resources/event.active_only')),

                Filter::make('date_range')
                    ->label(__('panels/admin/resources/event.date_range'))
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('date_from')
                            ->placeholder(__('panels/admin/resources/event.from_date')),
                        \Filament\Forms\Components\DatePicker::make('date_to')
                            ->placeholder(__('panels/admin/resources/event.to_date')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn(Builder $q) => $q->whereDate('date', '>=', $data['date_from'])
                            )
                            ->when(
                                $data['date_to'],
                                fn(Builder $q) => $q->whereDate('date', '<=', $data['date_to'])
                            );
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exporter(EventExporter::class)
                    ->columnMappingColumns(3),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->exporter(EventExporter::class)
                        ->columnMappingColumns(3),
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }
}
