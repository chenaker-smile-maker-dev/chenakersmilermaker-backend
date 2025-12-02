<?php

namespace App\Filament\Admin\Resources\Testimonials\Tables;

use App\Filament\Exports\TestimonialExporter;
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

class TestimonialsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('patient_name')
                    ->label('Patient Name')
                    ->searchable(['patient_name', 'patient.full_name'])
                    ->limit(50)
                    ->toggleable(),

                TextColumn::make('rating')
                    ->label('Rating')
                    ->formatStateUsing(fn($state) => str_repeat('⭐', $state))
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('content')
                    ->searchable()
                    ->limit(75)
                    ->tooltip(fn($record) => strip_tags($record->content))
                    ->placeholder('-')
                    ->toggleable(),

                IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->tooltip(fn($record) => $record->is_published ? 'Published' : 'Not Published')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('patient.first_name')
                    ->label('Linked Patient')
                    ->formatStateUsing(fn($record) => $record->patient?->full_name ?? '-')
                    ->searchable(['patient.first_name', 'patient.last_name'])
                    ->sortable()
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

                TernaryFilter::make('is_published')
                    ->label('Published Status')
                    ->placeholder('All testimonials')
                    ->trueLabel('Published only')
                    ->falseLabel('Unpublished only'),

                Filter::make('rating')
                    ->form([
                        \Filament\Forms\Components\Select::make('rating')
                            ->options([
                                1 => '1 Star',
                                2 => '2 Stars',
                                3 => '3 Stars',
                                4 => '4 Stars',
                                5 => '5 Stars',
                            ])
                            ->native(false)
                            ->placeholder('Select rating'),
                    ])
                    ->query(fn(Builder $query, array $data): Builder =>
                        $query->when(
                            $data['rating'] ?? null,
                            fn(Builder $q, $value) => $q->where('rating', $value)
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
                    ->exporter(TestimonialExporter::class)
                    ->columnMappingColumns(3),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->exporter(TestimonialExporter::class)
                        ->columnMappingColumns(3),
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }
}
