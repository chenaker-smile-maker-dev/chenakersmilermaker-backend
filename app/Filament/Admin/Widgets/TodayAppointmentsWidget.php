<?php

namespace App\Filament\Admin\Widgets;

use App\Filament\Admin\Resources\Appointments\AppointmentResource;
use App\Models\Appointment;
use Filament\Tables;
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
                    ->with(['doctor', 'patient', 'service'])
                    ->orderBy('from')
            )
            ->columns([
                Tables\Columns\TextColumn::make('from')
                    ->label('Time')
                    ->time('H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('patient.full_name')
                    ->label('Patient')
                    ->searchable(),
                Tables\Columns\TextColumn::make('doctor.display_name')
                    ->label('Doctor'),
                Tables\Columns\TextColumn::make('service.name')
                    ->label('Service'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->url(fn (Appointment $record) => AppointmentResource::getUrl('view', ['record' => $record]))
                    ->icon('heroicon-o-eye'),
            ])
            ->emptyStateHeading('No appointments today')
            ->emptyStateIcon('heroicon-o-calendar')
            ->paginated(false);
    }
}
