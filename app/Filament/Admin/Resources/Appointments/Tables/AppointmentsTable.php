<?php

namespace App\Filament\Admin\Resources\Appointments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AppointmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('doctor.display_name')
                    ->label(__('panels/admin/resources/appointment.doctor'))
                    ->searchable(),
                TextColumn::make('service.name')
                    ->label(__('panels/admin/resources/appointment.service'))
                    ->searchable(),
                TextColumn::make('patient.full_name')
                    ->label(__('panels/admin/resources/appointment.patient'))
                    ->searchable(),
                TextColumn::make('price')
                    ->label(__('panels/admin/resources/appointment.price'))
                    ->money('DZD')
                    ->badge()
                    ->alignCenter()
                    ->color("gray")
                    ->sortable(),
                TextColumn::make('from')
                    ->label(__('panels/admin/resources/appointment.from'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('to')
                    ->label(__('panels/admin/resources/appointment.to'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('panels/admin/resources/appointment.status'))
                    ->badge()
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label(__('panels/admin/resources/appointment.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('panels/admin/resources/appointment.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->label(__('panels/admin/resources/appointment.deleted_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
