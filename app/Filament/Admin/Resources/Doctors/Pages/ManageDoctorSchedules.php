<?php

namespace App\Filament\Admin\Resources\Doctors\Pages;

use App\Filament\Admin\Resources\Doctors\DoctorResource;
use App\Filament\Admin\Resources\Doctors\Schemas\DoctorInfolist;
use App\Filament\Admin\Resources\Doctors\Tables\SchedulesTable;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class ManageDoctorSchedules extends Page implements HasTable, HasInfolists
{
    use InteractsWithRecord;
    use InteractsWithTable;
    use InteractsWithInfolists;

    protected static string $resource = DoctorResource::class;

    protected string $view = 'panels.admin.resources.doctors.pages.manage-doctor-schedules';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function table(Table $table): Table
    {
        return SchedulesTable::configure(
            $table
                ->query(
                    $this->record
                        ->schedules()
                        ->with('periods')
                        ->getQuery()
                )
                ->modelLabel('Schedule')
                ->pluralModelLabel('Schedules')
                ->defaultSort('start_date', 'desc')
        );
    }

    public function infolist(Schema $schema): Schema
    {
        return DoctorInfolist::configure($schema)
            ->model($this->record);
    }


    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            EditAction::make(),
        ];
    }
}
