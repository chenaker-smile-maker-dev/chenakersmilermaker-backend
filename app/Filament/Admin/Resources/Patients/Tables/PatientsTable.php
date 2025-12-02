<?php

namespace App\Filament\Admin\Resources\Patients\Tables;

use App\Enums\Patient\Gender;
use App\Filament\Exports\PatientExporter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\NumberConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\SelectConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;

class PatientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->headerActions([
                ExportAction::make()
                    ->icon('heroicon-o-arrow-down-tray')
                    ->columnMappingColumns(3)
                    ->exporter(PatientExporter::class),
                CreateAction::make()
                    ->icon('heroicon-o-user-plus'),
            ])
            ->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('thumb_image')
                    ->toggleable()
                    ->circular(),
                TextColumn::make('full_name')
                    ->copyable()
                    ->toggleable(),
                TextColumn::make('first_name')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                TextColumn::make('last_name')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                TextColumn::make('email')
                    ->toggleable()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('phone')
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('gender')
                    ->badge()
                    ->alignCenter()
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('age')
                    ->alignCenter()
                    ->badge()
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->placeholder(__('panels/admin/resources/patient.not_deleted'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters(
                [
                    SelectFilter::make('gender')
                        ->options(Gender::class),
                    TrashedFilter::make(),
                    QueryBuilder::make()
                        ->columnSpanFull()
                        ->constraints([
                            TextConstraint::make('first_name'),
                            TextConstraint::make('last_name'),
                            TextConstraint::make('phone'),
                            TextConstraint::make('email'),
                            NumberConstraint::make('age'),
                            SelectConstraint::make('gender')
                                ->options(Gender::class),
                            DateConstraint::make('created_at'),
                            DateConstraint::make('updated_at'),
                            DateConstraint::make('deleted_at'),
                        ])
                ],
                layout: FiltersLayout::Dropdown
            )
            ->filtersFormColumns(3)
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->columnMappingColumns(3)
                        ->exporter(PatientExporter::class),
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
