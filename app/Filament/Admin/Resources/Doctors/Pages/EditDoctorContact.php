<?php

namespace App\Filament\Admin\Resources\Doctors\Pages;

use App\Filament\Admin\Resources\Doctors\DoctorResource;
use App\Filament\Admin\Resources\Doctors\Schemas\DoctorContactForm;
use App\Filament\Admin\Resources\Doctors\Schemas\DoctorForm;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;
use AymanAlhattami\FilamentPageWithSidebar\Traits\HasPageSidebar;

class EditDoctorContact extends EditRecord
{
    use HasPageSidebar;

    protected static string $resource = DoctorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    public  function form(Schema $schema): Schema
    {
        return DoctorContactForm::configure($schema);
    }
}
