<?php

namespace App\Filament\Admin\Resources\Doctors\Pages;

use App\Filament\Admin\Resources\Appointments\AppointmentResource;
use App\Filament\Admin\Resources\Doctors\DoctorResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables\Table;
use AymanAlhattami\FilamentPageWithSidebar\Traits\HasPageSidebar;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;

class ManageDoctorAppointments extends ManageRelatedRecords
{
    use HasPageSidebar;
    use InteractsWithRecord;
    protected static string $resource = DoctorResource::class;

    protected static string $relationship = 'appointments';

    protected static ?string $relatedResource = AppointmentResource::class;

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                // DeleteAction::make(),
                // ForceDeleteAction::make(),
                // RestoreAction::make(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            // ViewAction::make(),
            // EditAction::make(),
            // Action::make('manage_appointments')
            //     ->label("Manage Appointments")
            //     ->url(fn(): string => ManageDoctorAppointments::getUrl(['record' => $this->record])),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
