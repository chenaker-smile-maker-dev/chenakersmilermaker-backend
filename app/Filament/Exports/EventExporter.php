<?php

namespace App\Filament\Exports;

use App\Models\Event;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class EventExporter extends Exporter
{
    protected static ?string $model = Event::class;

    public static function getColumns(): array
    {
        $locales = config('default-local.available_locals');
        $title_with_locales = collect($locales)->map(function ($locale) {
            return ExportColumn::make('title_' . $locale)
                ->label('Title (' . strtoupper($locale) . ')')
                ->state(fn($record) => $record->getTranslation('title', $locale));
        });

        $description_with_locales = collect($locales)->map(function ($locale) {
            return ExportColumn::make('description_' . $locale)
                ->label('Description (' . strtoupper($locale) . ')')
                ->state(fn($record) => $record->getTranslation('description', $locale));
        });

        $location_with_locales = collect($locales)->map(function ($locale) {
            return ExportColumn::make('location_' . $locale)
                ->label('Location (' . strtoupper($locale) . ')')
                ->state(fn($record) => $record->getTranslation('location', $locale));
        });

        return [
            ExportColumn::make('id')
                ->label('ID'),
            ...$title_with_locales,
            ExportColumn::make('date')
                ->label('Date'),
            ...$description_with_locales,
            ...$location_with_locales,
            ExportColumn::make('is_archived')
                ->label('Archived')
                ->state(fn($record) => $record->is_archived ? 'Yes' : 'No'),
            ExportColumn::make('created_at')
                ->label('Created At'),
            ExportColumn::make('updated_at')
                ->label('Updated At'),
            ExportColumn::make('deleted_at')
                ->label('Deleted At'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $successful = $export->successful_rows;

        $body = 'Your events export is complete with ' . Number::format($successful) . ' ' . str('record')->plural($successful) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('record')->plural($failedRowsCount) . ' failed.';
        }

        return $body;
    }
}
