<?php

namespace App\Filament\Admin\Resources\Events\Pages;

use App\Filament\Admin\Resources\Events\EventResource;
use App\Models\Event;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListEvents extends ListRecords
{
    protected static string $resource = EventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all'       => Tab::make()
                ->badge(fn () => Event::count()),
            'future'    => Tab::make()
                ->badge(fn () => Event::future()->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn (Builder $query) => $query->future()),
            'happening' => Tab::make()
                ->badge(fn () => Event::happening()->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->happening()),
            'archive'   => Tab::make()
                ->badge(fn () => Event::archived()->count())
                ->badgeColor('gray')
                ->modifyQueryUsing(fn (Builder $query) => $query->archived()),
            'trashed'   => Tab::make()
                ->badge(fn () => Event::onlyTrashed()->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->onlyTrashed()),
        ];
    }
}
