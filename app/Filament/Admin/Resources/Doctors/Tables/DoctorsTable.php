<?php

namespace App\Filament\Admin\Resources\Doctors\Tables;

use App\Filament\Exports\DoctorExporter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class DoctorsTable
{
    private static string $layout = 'grid'; // 'grid' or 'table'

    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns(self::getColumns())
            ->headerActions([
                ExportAction::make()
                    ->icon('heroicon-o-arrow-down-tray')
                    ->columnMappingColumns(3)
                    ->exporter(DoctorExporter::class),
                CreateAction::make()
                    ->icon('heroicon-o-user-plus')
            ])
            ->when(
                self::$layout === 'grid',
                fn(Table $table) => $table->contentGrid([
                    'md' => 2,
                    'xl' => 3,
                ])
            )
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                // ForceDeleteAction::make(),
                // RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->columnMappingColumns(3)
                        ->exporter(DoctorExporter::class),
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Get columns based on the selected layout
     */
    private static function getColumns(): array
    {
        return match (self::$layout) {
            'grid' => self::getGridLayoutColumns(),
            'table' => self::getTableLayoutColumns(),
            default => self::getTableLayoutColumns(),
        };
    }

    /**
     * Grid layout columns (responsive cards)
     */
    private static function getGridLayoutColumns(): array
    {
        return [
            Split::make([
                ImageColumn::make('thumb_image')
                    ->circular()
                    ->size(80)
                    ->grow(false),

                Stack::make([
                    TextColumn::make("name")
                        ->weight(FontWeight::Bold)
                        ->searchable(),

                    TextColumn::make("specialty")
                        ->color('gray')
                        ->limit(50)
                        ->wrap(),
                ])->space(1),
            ])->from(''),
        ];
    }

    /**
     * Traditional table layout columns
     */
    private static function getTableLayoutColumns(): array
    {
        return [
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
                ->label('Diplomas'),

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
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }
}
