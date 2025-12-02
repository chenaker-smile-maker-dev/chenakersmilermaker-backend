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

                        TextEntry::make('full_name')
                            ->label(__('panels/admin/resources/patient.full_name'))
                            ->columnSpanFull(),
                        TextEntry::make('phone')
                            ->label(__('panels/admin/resources/patient.phone')),
                        TextEntry::make('email')
                            ->label(__('panels/admin/resources/patient.email_address')),
                        TextEntry::make('created_at')
                            ->label(__('panels/admin/resources/patient.created_at'))
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->label(__('panels/admin/resources/patient.updated_at'))
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('deleted_at')
                            ->label(__('panels/admin/resources/patient.deleted_at'))
                            ->placeholder(__('panels/admin/resources/patient.not_deleted'))
                            ->dateTime()
                            ->visible(fn(Patient $record): bool => $record->trashed()),
                    ])
            ]);
    }
}
