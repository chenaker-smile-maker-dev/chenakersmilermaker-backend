<?php

namespace App\Filament\Exports;

use App\Models\Doctor;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class DoctorExporter extends Exporter
{
    protected static ?string $model = Doctor::class;

    public static function getColumns(): array
    {
        $locals = config('default-local.available_locals');
        $name_with_locals =  collect($locals)->map(function ($locale) {
            return ExportColumn::make('name_' . $locale)
                ->state(fn($record) => $record->getTranslation('name', $locale));
        });
        $speciality_with_locals =  collect($locals)->map(function ($locale) {
            return ExportColumn::make('specialty_' . $locale)
                ->state(fn($record) => $record->getTranslation('specialty', $locale));
        });

        return [
            ExportColumn::make('id')
                ->label('ID'),
            ...$name_with_locals,
            ...$speciality_with_locals,
            // ExportColumn::make('diplomas'),
            ExportColumn::make('deleted_at'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $successful = $export->successful_rows;

        $body = 'Votre export de médecins est terminé et ' . Number::format($successful) . ' ' . str('ligne')->plural($successful) . ' ' . str('exportée')->plural($successful) . '.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $verb = $failedRowsCount === 1 ? "n'a pas pu être exportée" : "n'ont pas pu être exportées";
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('ligne')->plural($failedRowsCount) . ' ' . $verb . '.';
        }

        return $body;
    }
}
