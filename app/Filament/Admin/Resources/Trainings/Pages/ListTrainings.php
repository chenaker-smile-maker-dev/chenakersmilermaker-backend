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
            'all'     => Tab::make(__('panels/admin/resources/training.tabs.all'))
                ->icon('heroicon-o-queue-list')
                ->badge(fn() => Training::count()),
            'trashed' => Tab::make(__('panels/admin/resources/training.tabs.trashed'))
                ->icon('heroicon-o-trash')
                ->badge(fn() => Training::onlyTrashed()->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn(Builder $query) => $query->onlyTrashed()),
        ];
    }
}
