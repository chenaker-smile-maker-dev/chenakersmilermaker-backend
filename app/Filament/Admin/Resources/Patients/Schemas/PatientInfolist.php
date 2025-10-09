<?php

namespace App\Filament\Admin\Resources\Patients\Schemas;

use App\Models\Patient;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PatientInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([

                        TextEntry::make('full_name')->columnSpanFull(),
                        TextEntry::make('phone'),
                        TextEntry::make('email')
                            ->label('Email address'),
                        TextEntry::make('created_at')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('deleted_at')
                            ->placeholder("not deleted")
                            ->dateTime()
                            ->visible(fn(Patient $record): bool => $record->trashed()),
                    ])
            ]);
    }
}
