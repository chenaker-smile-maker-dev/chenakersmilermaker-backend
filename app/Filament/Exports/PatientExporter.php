<?php

namespace App\Filament\Exports;

use App\Models\Patient;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class PatientExporter extends Exporter
{
    protected static ?string $model = Patient::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('full_name'),
            ExportColumn::make('first_name'),
            ExportColumn::make('last_name'),
            ExportColumn::make('email'),
            ExportColumn::make('phone'),
            ExportColumn::make('age'),
            ExportColumn::make('gender')
                ->state(fn($record) => $record->gender->value),
            // ExportColumn::make('email_verified_at'),
            ExportColumn::make('deleted_at'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $successful = $export->successful_rows;

        $body = 'Votre export de patients est terminé et ' . Number::format($successful) . ' ' . str('ligne')->plural($successful) . ' ' . str('exportée')->plural($successful) . '.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $verb = $failedRowsCount === 1 ? "n'a pas pu être exportée" : "n'ont pas pu être exportées";
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('ligne')->plural($failedRowsCount) . ' ' . $verb . '.';
        }

        return $body;
    }
}
