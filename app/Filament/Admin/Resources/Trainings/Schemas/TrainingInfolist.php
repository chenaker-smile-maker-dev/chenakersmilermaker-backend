<?php

namespace App\Filament\Admin\Resources\Trainings\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TrainingInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('panels/admin/resources/training.training_overview'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('title')
                            ->columnSpanFull()
                            ->size('lg'),
                        TextEntry::make('trainer_name')
                            ->label(__('panels/admin/resources/training.instructor'))
                            ->placeholder('-'),
                        TextEntry::make('duration')
                            ->label(__('panels/admin/resources/training.duration'))
                            ->placeholder('-'),
                        TextEntry::make('description')
                            ->columnSpanFull()
                            ->html()
                            ->placeholder('-'),
                        TextEntry::make('video_url')
                            ->columnSpanFull()
                            ->url(fn($state) => $state)
                            ->openUrlInNewTab()
                            ->placeholder('-')
                            ->label(__('panels/admin/resources/training.training_video')),
                    ]),

                Section::make(__('panels/admin/resources/training.media_documents'))
                    ->columns(1)
                    ->schema([
                        ImageEntry::make('image')
                            ->columnSpanFull()
                            ->placeholder(__('panels/admin/resources/training.no_image_uploaded')),
                    ]),

                Section::make(__('panels/admin/resources/training.system_information'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('created_at')
                            ->label(__('panels/admin/resources/training.created'))
                            ->dateTime('F j, Y H:i')
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->label(__('panels/admin/resources/training.last_updated'))
                            ->dateTime('F j, Y H:i')
                            ->placeholder('-'),
                    ]),
            ]);
    }
}
