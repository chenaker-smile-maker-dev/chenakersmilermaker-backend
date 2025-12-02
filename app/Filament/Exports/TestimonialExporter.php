<?php

namespace App\Filament\Exports;

use App\Models\Testimonial;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class TestimonialExporter extends Exporter
{
    protected static ?string $model = Testimonial::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('name')
                ->label('Patient Name'),
            ExportColumn::make('patient.full_name')
                ->label('Linked Patient'),
            ExportColumn::make('rating')
                ->label('Rating'),
            ExportColumn::make('content')
                ->label('Review Content'),
            ExportColumn::make('is_published')
                ->label('Published')
                ->state(fn($record) => $record->is_published ? 'Yes' : 'No'),
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

        $body = 'Your testimonials export is complete with ' . Number::format($successful) . ' ' . str('record')->plural($successful) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('record')->plural($failedRowsCount) . ' failed.';
        }

        return $body;
    }
}
