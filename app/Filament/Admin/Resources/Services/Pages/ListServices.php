<?php

namespace App\Filament\Admin\Resources\Services\Pages;

use App\Filament\Admin\Resources\Services\ServiceResource;
use App\Filament\Admin\Resources\Services\Widgets\ServiceCards;
use App\Models\Service;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListServices extends ListRecords
{
    protected static string $resource = ServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ServiceCards::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'all'      => Tab::make(__('panels/admin/resources/service.tabs.all'))
                ->icon('heroicon-o-queue-list')
                ->badge(fn() => Service::count()),
            'active'   => Tab::make(__('panels/admin/resources/service.tabs.active'))
                ->icon('heroicon-o-check-circle')
                ->badge(fn() => Service::where('active', true)->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('active', true)),
            'inactive' => Tab::make(__('panels/admin/resources/service.tabs.inactive'))
                ->icon('heroicon-o-x-circle')
                ->badge(fn() => Service::where('active', false)->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('active', false)),
        ];
    }
}
