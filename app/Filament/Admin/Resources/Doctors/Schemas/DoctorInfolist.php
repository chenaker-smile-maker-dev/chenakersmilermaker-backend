<?php

namespace App\Filament\Admin\Resources\Doctors\Schemas;

use App\Models\Doctor;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Support\Enums\FontWeight;
use Illuminate\View\View;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DoctorInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(5)
            ->components([
                Section::make(function (Doctor $record): string {
                    return $record->display_name . ' - ' . $record->specialty;
                })
                    ->columnSpanFull()
                    ->columns(5)
                    ->schema([
                        Grid::make()
                            ->columnSpan(3)
                            ->columns(2)
                            ->schema([
                                TextEntry::make('email')
                                    ->label('Email')
                                    ->icon('heroicon-m-envelope')
                                    ->copyable()
                                    ->columnSpanFull(),
                                TextEntry::make('phone')
                                    ->label('Phone Number')
                                    ->icon('heroicon-m-phone')
                                    ->copyable()
                                    ->columnSpanFull(),
                                TextEntry::make('address')
                                    ->label('Address')
                                    ->icon('heroicon-m-map-pin')
                                    ->columnSpanFull(),
                                TextEntry::make('diplomas')
                                    ->label('Diplomas & Certifications')
                                    ->formatStateUsing(function ($state) {
                                        if (!is_array($state) || empty($state)) {
                                            return 'No qualifications listed';
                                        }
                                        return implode(', ', $state);
                                    })
                                    ->columnSpanFull(),
                            ]),
                        Grid::make()
                            ->columnSpan(2)
                            ->columns(1)
                            ->schema([
                                ImageEntry::make('image')
                                    // ->imageHeight("100%")
                                    ->extraImgAttributes(['style' => 'border-radius: 8px; max-width: 100%;'])
                                    ->label('')
                                    ->columnSpanFull(),
                            ]),
                    ]),
                Section::make('Additional Information')
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('metadata')
                            ->label('')
                            ->state(fn(Doctor $record) => self::renderMetadataTable($record->metadata ?? []))
                            ->html()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    private static function renderMetadataTable(array $metadata): string
    {
        if (empty($metadata)) {
            return '<p class="text-gray-500 dark:text-gray-400">No additional information</p>';
        }

        $html = '<div class="overflow-x-auto">';
        $html .= '<table class="w-full text-sm border-collapse">';
        $html .= '<thead class="bg-gray-100 dark:bg-gray-800">';
        $html .= '<tr>';
        $html .= '<th class="border border-gray-200 dark:border-gray-700 px-4 py-2 text-left font-semibold">Key</th>';
        $html .= '<th class="border border-gray-200 dark:border-gray-700 px-4 py-2 text-left font-semibold">Value</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';

        foreach ($metadata as $key => $value) {
            $formattedValue = is_array($value) ? implode(', ', $value) : $value;
            $html .= '<tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-900">';
            $html .= '<td class="border border-gray-200 dark:border-gray-700 px-4 py-3 font-medium">' . htmlspecialchars($key) . '</td>';
            $html .= '<td class="border border-gray-200 dark:border-gray-700 px-4 py-3">' . htmlspecialchars($formattedValue) . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '</div>';

        return $html;
    }
}
