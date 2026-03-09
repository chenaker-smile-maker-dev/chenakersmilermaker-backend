<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Appointment;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TodayAppointmentsWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = "Today's Appointments";

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Appointment::query()
                    ->whereDate('from', today())
                    ->with(['patient', 'doctor', 'service'])
                    ->orderBy('from')
            )
            ->columns([
                TextColumn::make('from')
                    ->label('Time')
                    ->time('H:i')
                    ->sortable(),
                TextColumn::make('patient.full_name')
                    ->label('Patient')
                    ->searchable(),
                TextColumn::make('doctor.display_name')
                    ->label('Doctor'),
                TextColumn::make('service.name')
                    ->label('Service'),
                TextColumn::make('status')
                    ->badge(),
            ])
            ->recordAction('view')
            ->recordUrl(fn(Appointment $record) => route('filament.admin.resources.appointments.view', $record))
            ->paginated(false);
    }
}
