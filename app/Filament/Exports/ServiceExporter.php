<?php

namespace App\Filament\Exports;

use App\Models\Service;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Number;

class ServiceExporter extends Exporter
{
    protected static ?string $model = Service::class;

    public static function getColumns(): array
    {
        $locals = config('default-local.available_locals');
        $name_with_locals = collect($locals)->map(function ($locale) {
            return ExportColumn::make('name_' . $locale)
                ->state(fn($record) => $record->getTranslation('name', $locale));
        });

        return [
            ExportColumn::make('id')
                ->label('ID'),
            ...$name_with_locals,
            ExportColumn::make('price')
                ->label('Price (DZD)'),
            // ExportColumn::make('availability')
            //     ->state(fn($record) => $record->availability->value),
            ExportColumn::make('active')
                ->label('Active')
                ->state(fn($record) => $record->active ? 'Yes' : 'No'),
            ExportColumn::make('created_at')
                ->label('Created At'),
            ExportColumn::make('updated_at')
                ->label('Updated At'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $successful = $export->successful_rows;

        $body = 'Votre export de services est terminé et ' . Number::format($successful) . ' ' . str('ligne')->plural($successful) . ' ' . str('exportée')->plural($successful) . '.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $verb = $failedRowsCount === 1 ? "n'a pas pu être exportée" : "n'ont pas pu être exportées";
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('ligne')->plural($failedRowsCount) . ' ' . $verb . '.';
        }

        return $body;
    }
}
