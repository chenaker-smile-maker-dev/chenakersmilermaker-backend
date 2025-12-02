<?php

namespace App\Filament\Admin\Resources\Services\Tables;

use App\Enums\Service\ServiceAvailability;
use App\Filament\Exports\ServiceExporter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ServicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('image')
                    ->collection('image')
                    ->conversion('thumb')
                    ->circular()
                    ->placeholder(__('panels/admin/resources/service.no_image_uploaded'))
                    ->toggleable(),
                TextColumn::make('name')
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('price')
                    ->toggleable()
                    ->money("DZD")
                    ->sortable(),
                TextColumn::make('availability')
                    ->toggleable()
                    ->badge()
                    ->sortable()
                    ->searchable(),
                IconColumn::make('active')
                    ->sortable()
                    ->toggleable()
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('active')
                    ->options([
                        '1' => __('panels/admin/resources/service.active'),
                        '0' => __('panels/admin/resources/service.inactive'),
                    ])
                    ->label(__('panels/admin/resources/service.status')),
                SelectFilter::make('availability')
                    ->options(ServiceAvailability::class),
                Filter::make('created_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('created_from')
                            ->placeholder(__('panels/admin/resources/service.from')),
                        \Filament\Forms\Components\DatePicker::make('created_until')
                            ->placeholder(__('panels/admin/resources/service.until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->label(__('panels/admin/resources/service.created_date_range')),
            ])
            ->headerActions([
                ExportAction::make()
                    ->icon('heroicon-o-arrow-down-tray')
                    ->columnMappingColumns(3)
                    ->exporter(ServiceExporter::class),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->columnMappingColumns(3)
                        ->exporter(ServiceExporter::class),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
