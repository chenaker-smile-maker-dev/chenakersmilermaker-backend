<?php

namespace App\Filament\Admin\Resources\Appointments\Schemas;

use App\Enums\Appointment\AppointmentStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AppointmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DateTimePicker::make('from')
                    ->required(),
                DateTimePicker::make('to')
                    ->required(),
                Select::make('doctor_id')
                    ->relationship('doctor', 'name'),
                Select::make('service_id')
                    ->relationship('service', 'name'),
                Select::make('patient_id')
                    ->relationship('patient', 'id'),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                Select::make('status')
                    ->options(AppointmentStatus::class)
                    ->default('pending')
                    ->required(),
                TextInput::make('metadata'),
            ]);
    }
}
