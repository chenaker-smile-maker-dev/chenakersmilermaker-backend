<?php

namespace App\Filament\Admin\Resources\Trainings\Pages;

use App\Filament\Admin\Resources\Trainings\TrainingResource;
use App\Models\Training;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListTrainings extends ListRecords
{
    protected static string $resource = TrainingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all'     => Tab::make()
                ->badge(fn () => Training::count()),
            'trashed' => Tab::make()
                ->badge(fn () => Training::onlyTrashed()->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->onlyTrashed()),
        ];
    }
}
