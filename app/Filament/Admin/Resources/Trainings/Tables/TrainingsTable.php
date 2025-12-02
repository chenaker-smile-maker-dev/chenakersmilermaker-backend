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
                    ->circular()
                    ->placeholder('No image')
                    ->toggleable(),

                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->toggleable(),

                TextColumn::make('trainer_name')
                    ->label('Trainer')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-user')
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('duration')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-clock')
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('description')
                    ->searchable()
                    ->limit(60)
                    ->tooltip(fn($record) => $record->getTranslation('description', app()->getLocale()))
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('deleted_at')
                    ->label('Deleted')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->placeholder('Not deleted')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),

                Filter::make('trainer_name')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('trainer_name')
                            ->placeholder('Search by trainer name'),
                    ])
                    ->query(fn(Builder $query, array $data): Builder =>
                        $query->when(
                            $data['trainer_name'],
                            fn(Builder $q) => $q->whereILike('trainer_name', '%' . $data['trainer_name'] . '%')
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
