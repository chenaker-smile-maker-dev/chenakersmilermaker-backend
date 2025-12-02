<?php

namespace App\Filament\Admin\Resources\Testimonials\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class TestimonialInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make(__('panels/admin/resources/testimonial.testimonial_overview'))
                ->columns(2)
                ->schema([
                    TextEntry::make('patient_name')
                        ->label(__('panels/admin/resources/testimonial.patient_name'))
                        ->size('lg')
                        ->columnSpanFull(),

                    TextEntry::make('patient.first_name')
                        ->label(__('panels/admin/resources/testimonial.linked_patient'))
                        ->url(fn($record) => $record->patient_id ? route('filament.admin.resources.patients.edit', $record->patient) : null)
                        ->openUrlInNewTab()
                        ->placeholder(__('panels/admin/resources/testimonial.no_patient_linked'))
                        ->formatStateUsing(fn($record) => $record->patient?->full_name ?? ''),

                    TextEntry::make('rating')
                        ->label(__('panels/admin/resources/testimonial.rating'))
                        ->formatStateUsing(fn($state) => str_repeat('⭐', $state)),

                    IconEntry::make('is_published')
                        ->label(__('panels/admin/resources/testimonial.published'))
                        ->boolean()
                        ->trueIcon('heroicon-o-check-circle')
                        ->falseIcon('heroicon-o-x-circle')
                        ->trueColor('success')
                        ->falseColor('warning'),

                    TextEntry::make('content')
                        ->label(__('panels/admin/resources/testimonial.review'))
                        ->html()
                        ->columnSpanFull(),
                ]),

            Section::make(__('panels/admin/resources/testimonial.system_information'))
                ->columns(2)
                ->schema([
                    TextEntry::make('created_at')
                        ->label(__('panels/admin/resources/testimonial.created_at'))
                        ->dateTime('F j, Y H:i'),

                    TextEntry::make('updated_at')
                        ->label(__('panels/admin/resources/testimonial.updated_at'))
                        ->dateTime('F j, Y H:i'),

                    TextEntry::make('deleted_at')
                        ->label(__('panels/admin/resources/testimonial.deleted_at'))
                        ->dateTime('F j, Y H:i')
                        ->placeholder(__('panels/admin/resources/testimonial.not_deleted')),
                ]),
        ]);
    }
}
