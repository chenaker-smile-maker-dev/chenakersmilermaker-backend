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
            Section::make('Testimonial Overview')
                ->columns(2)
                ->schema([
                    TextEntry::make('patient_name')
                        ->label('Patient Name')
                        ->size('lg')
                        ->columnSpanFull(),

                    TextEntry::make('patient.first_name')
                        ->label('Linked Patient')
                        ->url(fn($record) => $record->patient_id ? route('filament.admin.resources.patients.edit', $record->patient) : null)
                        ->openUrlInNewTab()
                        ->placeholder('No patient linked')
                        ->formatStateUsing(fn($record) => $record->patient?->full_name ?? ''),

                    TextEntry::make('rating')
                        ->label('Rating')
                        ->formatStateUsing(fn($state) => str_repeat('⭐', $state)),

                    IconEntry::make('is_published')
                        ->label('Published')
                        ->boolean()
                        ->trueIcon('heroicon-o-check-circle')
                        ->falseIcon('heroicon-o-x-circle')
                        ->trueColor('success')
                        ->falseColor('warning'),

                    TextEntry::make('content')
                        ->label('Review')
                        ->html()
                        ->columnSpanFull(),
                ]),

            Section::make('System Information')
                ->columns(2)
                ->schema([
                    TextEntry::make('created_at')
                        ->label('Created At')
                        ->dateTime('F j, Y H:i'),

                    TextEntry::make('updated_at')
                        ->label('Updated At')
                        ->dateTime('F j, Y H:i'),

                    TextEntry::make('deleted_at')
                        ->label('Deleted At')
                        ->dateTime('F j, Y H:i')
                        ->placeholder('Not deleted'),
                ]),
        ]);
    }
}
