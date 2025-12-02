<?php

namespace App\Filament\Admin\Resources\Trainings\Tables;

use App\Filament\Exports\TrainingExporter;
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
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TrainingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label(__('panels/admin/resources/training.image'))
                    ->circular()
                    ->placeholder(__('panels/admin/resources/training.no_image'))
                    ->toggleable(),

                TextColumn::make('title')
                    ->label(__('panels/admin/resources/training.title'))
                    ->searchable()
                    ->limit(50)
                    ->toggleable(),

                TextColumn::make('trainer_name')
                    ->label(__('panels/admin/resources/training.trainer'))
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-user')
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('duration')
                    ->label(__('panels/admin/resources/training.duration'))
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-clock')
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('description')
                    ->label(__('panels/admin/resources/training.description'))
                    ->searchable()
                    ->limit(60)
                    ->tooltip(fn($record) => $record->getTranslation('description', app()->getLocale()))
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label(__('panels/admin/resources/training.created'))
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label(__('panels/admin/resources/training.updated'))
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('deleted_at')
                    ->label(__('panels/admin/resources/training.deleted'))
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->placeholder(__('panels/admin/resources/training.not_deleted'))
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),

                Filter::make('trainer_name')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('trainer_name')
                            ->placeholder(__('panels/admin/resources/training.search_by_trainer_name')),
                    ])
                    ->query(fn(Builder $query, array $data): Builder =>
                        $query->when(
                            $data['trainer_name'] ?? null,
                            fn(Builder $q, $value) => $q->where('trainer_name', 'like', '%' . $value . '%')
                        )
                    ),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exporter(TrainingExporter::class)
                    ->columnMappingColumns(3),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->exporter(TrainingExporter::class)
                        ->columnMappingColumns(3),
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }
}
