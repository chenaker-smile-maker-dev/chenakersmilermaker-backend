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
                    ->label(__('panels/admin/resources/testimonial.patient_name'))
                    ->searchable(['patient_name', 'patient.full_name'])
                    ->limit(50)
                    ->toggleable(),

                TextColumn::make('rating')
                    ->label(__('panels/admin/resources/testimonial.rating'))
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
                    ->label(__('panels/admin/resources/testimonial.published'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->tooltip(fn($record) => $record->is_published ? __('panels/admin/resources/testimonial.published') : __('panels/admin/resources/testimonial.not_published'))
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('patient.first_name')
                    ->label(__('panels/admin/resources/testimonial.linked_patient'))
                    ->formatStateUsing(fn($record) => $record->patient?->full_name ?? '-')
                    ->searchable(['patient.first_name', 'patient.last_name'])
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label(__('panels/admin/resources/testimonial.created'))
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label(__('panels/admin/resources/testimonial.updated'))
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('deleted_at')
                    ->label(__('panels/admin/resources/testimonial.deleted'))
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->placeholder(__('panels/admin/resources/testimonial.not_deleted'))
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),

                TernaryFilter::make('is_published')
                    ->label(__('panels/admin/resources/testimonial.published_status'))
                    ->placeholder(__('panels/admin/resources/testimonial.all_testimonials'))
                    ->trueLabel(__('panels/admin/resources/testimonial.published_only'))
                    ->falseLabel(__('panels/admin/resources/testimonial.unpublished_only')),

                Filter::make('rating')
                    ->form([
                        \Filament\Forms\Components\Select::make('rating')
                            ->options([
                                1 => __('panels/admin/resources/testimonial.rating_filter.1'),
                                2 => __('panels/admin/resources/testimonial.rating_filter.2'),
                                3 => __('panels/admin/resources/testimonial.rating_filter.3'),
                                4 => __('panels/admin/resources/testimonial.rating_filter.4'),
                                5 => __('panels/admin/resources/testimonial.rating_filter.5'),
                            ])
                            ->native(false)
                            ->placeholder(__('panels/admin/resources/testimonial.select_rating')),
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
