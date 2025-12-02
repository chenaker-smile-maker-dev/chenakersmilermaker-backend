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
                            ->label(__('panels/admin/resources/appointment.from'))
                            ->required(),
                        DateTimePicker::make('to')
                            ->label(__('panels/admin/resources/appointment.to'))
                            ->required(),
                        Select::make('doctor_id')
                            ->label(__('panels/admin/resources/appointment.doctor'))
                            ->relationship('doctor', 'name'),
                        Select::make('service_id')
                            ->label(__('panels/admin/resources/appointment.service'))
                            ->relationship('service', 'name'),
                        // Select::make('patient_id')
                        //     ->searchable()
                        //     ->relationship('patient', 'email')
                        //     ->getOptionLabelUsing(fn($value, $record) => $record?->patient?->full_name),
                        Select::make('patient_id')
                            ->label(__('panels/admin/resources/appointment.patient'))
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
                            ->label(__('panels/admin/resources/appointment.price'))
                            ->required()
                            ->numeric()
                            ->suffix('DZD'),
                        Select::make('status')
                            ->label(__('panels/admin/resources/appointment.status'))
                            ->options(AppointmentStatus::class)
                            ->default('pending')
                            ->required(),
                        TextInput::make('metadata')
                            ->label(__('panels/admin/resources/appointment.metadata')),
                    ])
            ]);
    }
}
