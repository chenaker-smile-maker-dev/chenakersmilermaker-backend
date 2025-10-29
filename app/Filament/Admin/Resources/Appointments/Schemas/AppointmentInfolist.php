<?php

namespace App\Filament\Admin\Resources\Appointments\Schemas;

use App\Models\Appointment;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AppointmentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('from')
                    ->dateTime(),
                TextEntry::make('to')
                    ->dateTime(),
                TextEntry::make('doctor.name')
                    ->label('Doctor')
                    ->placeholder('-'),
                TextEntry::make('service.name')
                    ->label('Service')
                    ->placeholder('-'),
                TextEntry::make('patient.id')
                    ->label('Patient')
                    ->placeholder('-'),
                TextEntry::make('price')
                    ->money(),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Appointment $record): bool => $record->trashed()),
            ]);
    }
}
