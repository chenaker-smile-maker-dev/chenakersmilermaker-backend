<?php

namespace App\Filament\Admin\Resources\Doctors\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class DoctorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('doctor_photo')
                    ->collection('doctor_photo')
                    ->conversion('thumb')
                    ->circular(),

                TextColumn::make("name")
                    ->searchable()
                    ->toggleable(),

                TextColumn::make("specialty")
                    ->limit(50)
                    ->wrap()
                    ->sortable()
                    ->toggleable(),

                // diplomas, number of diplomas
                TextColumn::make("diplomas_count")
                    ->badge()
                    ->sortable()
                    ->toggleable()
                    ->color("primary")
                    ->alignCenter(),

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
                    ->placeholder('Not deleted')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // TextColumn::make('deleted_at')
                //     ->dateTime()
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),
                // TextColumn::make('created_at')
                //     ->dateTime()
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),
                // TextColumn::make('updated_at')
                //     ->dateTime()
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
