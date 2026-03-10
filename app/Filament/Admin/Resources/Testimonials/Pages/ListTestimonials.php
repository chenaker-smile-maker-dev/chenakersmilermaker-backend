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
            'all'         => Tab::make()
                ->badge(fn () => Testimonial::count()),
            'published'   => Tab::make()
                ->badge(fn () => Testimonial::where('is_published', true)->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_published', true)),
            'unpublished' => Tab::make()
                ->badge(fn () => Testimonial::where('is_published', false)->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_published', false)),
            'trashed'     => Tab::make()
                ->badge(fn () => Testimonial::onlyTrashed()->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->onlyTrashed()),
        ];
    }
}
