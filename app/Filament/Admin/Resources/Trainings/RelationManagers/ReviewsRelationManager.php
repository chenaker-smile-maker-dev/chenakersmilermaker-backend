<?php

namespace App\Filament\Admin\Resources\Trainings\RelationManagers;

use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Actions\Action as TableAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReviewsRelationManager extends RelationManager
{
    protected static string $relationship = 'reviews';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('panels/admin/resources/training.reviews_tab');
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('reviewer_name')
                    ->label(__('panels/admin/resources/review.reviewer_name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('rating')
                    ->label(__('panels/admin/resources/review.rating'))
                    ->badge()
                    ->color(fn(int $state): string => match (true) {
                        $state >= 4 => 'success',
                        $state === 3 => 'warning',
                        default     => 'danger',
                    })
                    ->formatStateUsing(fn(int $state): string => str_repeat('★', $state) . str_repeat('☆', 5 - $state))
                    ->sortable(),

                TextColumn::make('content')
                    ->label(__('panels/admin/resources/review.content'))
                    ->limit(80)
                    ->tooltip(fn(string $state): string => $state)
                    ->wrap(),

                IconColumn::make('is_approved')
                    ->label(__('panels/admin/resources/review.is_approved'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('panels/admin/resources/review.submitted_at'))
                    ->dateTime()
                    ->sortable()
                    ->since(),
            ])
            ->filters([
                Filter::make('pending')
                    ->label(__('panels/admin/resources/review.filter_pending'))
                    ->query(fn(Builder $query): Builder => $query->where('is_approved', false))
                    ->default(),

                Filter::make('approved')
                    ->label(__('panels/admin/resources/review.filter_approved'))
                    ->query(fn(Builder $query): Builder => $query->where('is_approved', true)),
            ])
            ->actions([
                TableAction::make('approve')
                    ->label(__('panels/admin/resources/review.approve'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn($record): bool => ! $record->is_approved)
                    ->requiresConfirmation()
                    ->action(fn($record) => $record->update(['is_approved' => true])),

                TableAction::make('unapprove')
                    ->label(__('panels/admin/resources/review.unapprove'))
                    ->icon('heroicon-o-x-circle')
                    ->color('warning')
                    ->visible(fn($record): bool => $record->is_approved)
                    ->requiresConfirmation()
                    ->action(fn($record) => $record->update(['is_approved' => false])),

                DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\BulkAction::make('approve_selected')
                        ->label(__('panels/admin/resources/review.approve_selected'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn($records) => $records->each->update(['is_approved' => true])),

                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
