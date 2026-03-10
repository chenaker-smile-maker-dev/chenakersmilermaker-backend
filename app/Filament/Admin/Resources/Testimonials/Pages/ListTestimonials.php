<?php

namespace App\Filament\Admin\Resources\Testimonials\Pages;

use App\Filament\Admin\Resources\Testimonials\TestimonialResource;
use App\Models\Testimonial;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListTestimonials extends ListRecords
{
    protected static string $resource = TestimonialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all'         => Tab::make(__('panels/admin/resources/testimonial.tabs.all'))
                ->icon('heroicon-o-queue-list')
                ->badge(fn() => Testimonial::count()),
            'published'   => Tab::make(__('panels/admin/resources/testimonial.tabs.published'))
                ->icon('heroicon-o-eye')
                ->badge(fn() => Testimonial::where('is_published', true)->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('is_published', true)),
            'unpublished' => Tab::make(__('panels/admin/resources/testimonial.tabs.unpublished'))
                ->icon('heroicon-o-eye-slash')
                ->badge(fn() => Testimonial::where('is_published', false)->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('is_published', false)),
            'trashed'     => Tab::make(__('panels/admin/resources/testimonial.tabs.trashed'))
                ->icon('heroicon-o-trash')
                ->badge(fn() => Testimonial::onlyTrashed()->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn(Builder $query) => $query->onlyTrashed()),
        ];
    }
}
