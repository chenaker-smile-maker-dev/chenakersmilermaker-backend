<?php

namespace App\Filament\Admin\Resources\Trainings\Pages;

use App\Filament\Admin\Resources\Trainings\TrainingResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTraining extends ViewRecord
{
    protected static string $resource = TrainingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
