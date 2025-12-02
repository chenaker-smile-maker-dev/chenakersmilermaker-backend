<?php

namespace App\Filament\Admin\Resources\Patients\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PatientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([

                        TextInput::make('first_name')
                            ->label(__('panels/admin/resources/patient.first_name'))
                            ->required(),
                        TextInput::make('last_name')
                            ->label(__('panels/admin/resources/patient.last_name'))
                            ->required(),
                        TextInput::make('phone')
                            ->label(__('panels/admin/resources/patient.phone'))
                            // ->tel()
                            ->required(),
                        TextInput::make('email')
                            ->label(__('panels/admin/resources/patient.email_address'))
                            ->email()
                            ->required(),
                        TextInput::make('password')
                            ->label(__('panels/admin/resources/patient.password'))
                            ->password()
                            ->visibleOn('create')
                            ->required(),
                    ])->columnSpanFull()->columns(2)
            ]);
    }
}
