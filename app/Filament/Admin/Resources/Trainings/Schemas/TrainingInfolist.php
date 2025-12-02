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
                Section::make('Training Overview')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('title')
                            ->columnSpanFull()
                            ->size('lg'),
                        TextEntry::make('trainer_name')
                            ->label('Instructor')
                            ->placeholder('-'),
                        TextEntry::make('duration')
                            ->label('Duration')
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
                            ->label('Training Video'),
                    ]),

                Section::make('Media & Documents')
                    ->columns(1)
                    ->schema([
                        ImageEntry::make('image')
                            ->columnSpanFull()
                            ->placeholder('No image uploaded'),
                    ]),

                Section::make('System Information')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime('F j, Y H:i')
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime('F j, Y H:i')
                            ->placeholder('-'),
                    ]),
            ]);
    }
}
