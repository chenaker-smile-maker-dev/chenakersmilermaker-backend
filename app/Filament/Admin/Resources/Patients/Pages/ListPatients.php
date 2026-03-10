<?php

namespace App\Filament\Admin\Resources\Patients\Pages;

use App\Enums\Patient\Gender;
use App\Filament\Admin\Resources\Patients\PatientResource;
use App\Filament\Admin\Resources\Patients\Widgets\PatientCards;
use App\Models\Patient;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListPatients extends ListRecords
{
    protected static string $resource = PatientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            PatientCards::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'all'    => Tab::make()
                ->badge(fn () => Patient::count()),
            'male'   => Tab::make()
                ->badge(fn () => Patient::where('gender', Gender::MALE)->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('gender', Gender::MALE)),
            'female' => Tab::make()
                ->badge(fn () => Patient::where('gender', Gender::FEMALE)->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('gender', Gender::FEMALE)),
        ];
    }
}
