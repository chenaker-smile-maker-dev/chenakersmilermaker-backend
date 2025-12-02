<?php

namespace App\Filament\Admin\Resources\Services\RelationManagers;

use App\Filament\Admin\Resources\Doctors\DoctorResource;
use App\Filament\Exports\DoctorExporter;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;

class DoctorsRelationManager extends RelationManager
{
    protected static string $relationship = 'doctors';

    protected static ?string $relatedResource = DoctorResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('doctors.created_at', 'desc')
            ->headerActions([
                ExportAction::make()
                    ->icon('heroicon-o-arrow-down-tray')
                    ->columnMappingColumns(3)
                    ->exporter(DoctorExporter::class),
                CreateAction::make(),
            ])
            ->columns([
                ImageColumn::make('thumb_image')
                    ->toggleable()
                    ->circular(),

                TextColumn::make("name")
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make("specialty")
                    ->limit(50)
                    ->wrap()
                    ->toggleable(),

                TextColumn::make("diplomas_count")
                    ->badge()
                    ->toggleable()
                    ->color("primary")
                    ->alignCenter()
                    ->label(__('panels/admin/resources/service.diplomas')),

                TextColumn::make('created_at')
                    ->formatStateUsing(fn($state) => $state ? $state->diffForHumans() : '—')
                    ->tooltip(fn($record) => $record->created_at?->format('Y-m-d H:i:s'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->formatStateUsing(fn($state) => $state ? $state->diffForHumans() : '—')
                    ->tooltip(fn($record) => $record->updated_at?->format('Y-m-d H:i:s'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('deleted_at')
                    ->formatStateUsing(fn($state) => $state ? $state->diffForHumans() : '—')
                    ->tooltip(fn($record) => $record->deleted_at?->format('Y-m-d H:i:s'))
                    ->placeholder(__('panels/admin/resources/service.not_deleted'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ]);
    }
}
