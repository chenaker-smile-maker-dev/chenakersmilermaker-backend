<?php

namespace App\Filament\Admin\Resources\Doctors\Pages;

use App\Filament\Admin\Resources\Doctors\DoctorResource;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;

class ManageDoctorSchedules extends Page
{
    use InteractsWithRecord;

    protected static string $resource = DoctorResource::class;

    protected string $view = 'panels.admin.resources.doctors.pages.manage-doctor-schedules';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }
}
