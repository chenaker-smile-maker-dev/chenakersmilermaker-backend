<?php

namespace App\Filament\Admin\Resources\Doctors\Pages;

use App\Filament\Admin\Resources\Doctors\DoctorResource;
use AymanAlhattami\FilamentPageWithSidebar\Traits\HasPageSidebar;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;

class DoctorCalendarPage extends Page
{
    use HasPageSidebar;
    use InteractsWithRecord;

    protected static string $resource = DoctorResource::class;
    protected string $view = 'panels.admin.resources.doctors.pages.doctor-calendar';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function getTitle(): string
    {
        return __('panels/admin/resources/doctor.calendar.nav_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('panels/admin/resources/doctor.calendar.nav_label');
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
