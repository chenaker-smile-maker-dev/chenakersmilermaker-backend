<?php

namespace App\Filament\Admin\Resources\Appointments\Pages;

use App\Enums\Appointment\AppointmentStatus;
use App\Filament\Admin\Resources\Appointments\AppointmentResource;
use App\Models\Appointment;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListAppointments extends ListRecords
{
    protected static string $resource = AppointmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all'       => Tab::make(__('panels/admin/resources/appointment.tabs.all'))
                ->icon('heroicon-o-queue-list')
                ->badge(fn() => Appointment::count()),
            'pending'   => Tab::make(__('panels/admin/resources/appointment.tabs.pending'))
                ->icon('heroicon-o-clock')
                ->badge(fn() => Appointment::where('status', AppointmentStatus::PENDING)->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', AppointmentStatus::PENDING)),
            'confirmed' => Tab::make(__('panels/admin/resources/appointment.tabs.confirmed'))
                ->icon('heroicon-o-check-circle')
                ->badge(fn() => Appointment::where('status', AppointmentStatus::CONFIRMED)->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', AppointmentStatus::CONFIRMED)),
            'rejected'  => Tab::make(__('panels/admin/resources/appointment.tabs.rejected'))
                ->icon('heroicon-o-x-circle')
                ->badge(fn() => Appointment::where('status', AppointmentStatus::REJECTED)->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', AppointmentStatus::REJECTED)),
            'cancelled' => Tab::make(__('panels/admin/resources/appointment.tabs.cancelled'))
                ->icon('heroicon-o-minus-circle')
                ->badge(fn() => Appointment::where('status', AppointmentStatus::CANCELLED)->count())
                ->badgeColor('gray')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', AppointmentStatus::CANCELLED)),
            'completed' => Tab::make(__('panels/admin/resources/appointment.tabs.completed'))
                ->icon('heroicon-o-check-badge')
                ->badge(fn() => Appointment::where('status', AppointmentStatus::COMPLETED)->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', AppointmentStatus::COMPLETED)),
        ];
    }
}
