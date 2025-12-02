<?php

namespace App\Filament\Admin\Resources\Testimonials\Pages;

use App\Filament\Admin\Resources\Testimonials\TestimonialResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTestimonial extends ViewRecord
{
    protected static string $resource = TestimonialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
