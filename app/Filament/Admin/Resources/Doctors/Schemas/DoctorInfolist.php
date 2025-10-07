<?php

namespace App\Filament\Admin\Resources\Doctors\Schemas;

use App\Models\Doctor;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DoctorInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make("doctor_information")
                    ->columnSpanFull()
                    ->columns(5)
                    ->schema([
                        Grid::make()
                            ->columnSpan(4)
                            ->columns(2)
                            ->schema([
                                TextEntry::make('id'),
                                TextEntry::make('name'),
                                TextEntry::make('specialty'),
                            ]),
                        Grid::make()
                            ->columnSpan(1)
                            ->columns(1)
                            ->schema([
                                ImageEntry::make('image')
                                    ->label("doctor photo")
                                    ->width("100%")
                                    ->height("auto"),
                            ])
                    ]),
                Section::make("timestamps")
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('created_at')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('deleted_at')
                            ->dateTime()
                            ->visible(fn(Doctor $record): bool => $record->trashed()),
                    ]),
            ]);
    }
}
