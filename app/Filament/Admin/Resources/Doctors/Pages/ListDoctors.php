<?php

namespace App\Filament\Admin\Resources\Doctors\Pages;

use App\Filament\Admin\Resources\Doctors\DoctorResource;
use App\Filament\Admin\Resources\Doctors\Widgets\DoctorCards;
use App\Models\Doctor;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListDoctors extends ListRecords
{
    protected static string $resource = DoctorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            DoctorCards::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'all'     => Tab::make(__('panels/admin/resources/doctor.tabs.all'))
                ->icon('heroicon-o-queue-list')
                ->badge(fn() => Doctor::count()),
            'trashed' => Tab::make(__('panels/admin/resources/doctor.tabs.trashed'))
                ->icon('heroicon-o-trash')
                ->badge(fn() => Doctor::onlyTrashed()->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn(Builder $query) => $query->onlyTrashed()),
        ];
    }
}
