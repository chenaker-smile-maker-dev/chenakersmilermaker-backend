<?php

namespace App\Filament\Exports;

use App\Models\Training;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class TrainingExporter extends Exporter
{
    protected static ?string $model = Training::class;

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

        return [
            ExportColumn::make('id')
                ->label('ID'),
            ...$title_with_locales,
            ExportColumn::make('trainer_name')
                ->label('Trainer'),
            ExportColumn::make('duration')
                ->label('Duration'),
            ExportColumn::make('video_url')
                ->label('Video URL'),
            ...$description_with_locales,
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

        $body = 'Your trainings export is complete with ' . Number::format($successful) . ' ' . str('record')->plural($successful) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('record')->plural($failedRowsCount) . ' failed.';
        }

        return $body;
    }
}
