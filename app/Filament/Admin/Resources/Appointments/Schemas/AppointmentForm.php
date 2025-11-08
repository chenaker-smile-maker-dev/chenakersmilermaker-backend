<?php

namespace App\Filament\Admin\Resources\Appointments\Schemas;

use App\Enums\Appointment\AppointmentStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AppointmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([

                        DateTimePicker::make('from')
                            ->required(),
                        DateTimePicker::make('to')
                            ->required(),
                        Select::make('doctor_id')
                            ->relationship('doctor', 'name'),
                        Select::make('service_id')
                            ->relationship('service', 'name'),
                        // Select::make('patient_id')
                        //     ->searchable()
                        //     ->relationship('patient', 'email')
                        //     ->getOptionLabelUsing(fn($value, $record) => $record?->patient?->full_name),
                        Select::make('patient_id')
                            ->searchable()
                            ->preload()
                            ->getSearchResultsUsing(
                                fn(string $search) =>
                                \App\Models\Patient::query()
                                    ->where(function ($query) use ($search) {
                                        $query->where('first_name', 'like', "%{$search}%")
                                            ->orWhere('last_name', 'like', "%{$search}%")
                                            ->orWhere('email', 'like', "%{$search}%");
                                    })
                                    ->limit(50)
                                    ->get()
                                    ->mapWithKeys(fn($patient) => [$patient->id => $patient->full_name])
                            )
                            ->getOptionLabelUsing(fn($value) => \App\Models\Patient::find($value)?->full_name),

                        TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->suffix('DZD'),
                        Select::make('status')
                            ->options(AppointmentStatus::class)
                            ->default('pending')
                            ->required(),
                        TextInput::make('metadata'),
                    ])
            ]);
    }
}
