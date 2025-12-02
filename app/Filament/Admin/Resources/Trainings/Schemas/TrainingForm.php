<?php

namespace App\Filament\Admin\Resources\Trainings\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use AbdulmajeedJamaan\FilamentTranslatableTabs\TranslatableTabs;

class TrainingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TranslatableTabs::make('translatable_data')
                ->columnSpanFull()
                ->schema([
                    TextInput::make('title')
                        ->required()
                        ->placeholder('Enter training title')
                        ->maxLength(255)
                        ->columnSpanFull(),
                    RichEditor::make('description')
                        ->columnSpanFull()
                        ->placeholder('Describe the training content, learning outcomes, and course structure')
                        ->extraAttributes(['style' => 'min-height: 300px;'])
                        ->disableAllToolbarButtons()
                        ->toolbarButtons([
                            'attachFiles',
                            'blockquote',
                            'bold',
                            'bulletList',
                            'codeBlock',
                            'italic',
                            'link',
                            'orderedList',
                            'redo',
                            'strike',
                            'undo',
                        ]),
                ]),

            Section::make('Training Information')
                ->columns(2)
                ->schema([
                    TextInput::make('trainer_name')
                        ->required()
                        ->placeholder('Full name of the instructor')
                        ->maxLength(255),
                    TextInput::make('duration')
                        ->required()
                        ->placeholder('e.g., 5 days, 2 weeks, 40 hours')
                        ->maxLength(255),
                    TextInput::make('video_url')
                        ->url()
                        ->columnSpanFull()
                        ->placeholder('https://example.com/video')
                        ->helperText('Link to the training video (optional)'),
                ]),

            Section::make('Media')
                ->columns(1)
                ->schema([
                    FileUpload::make('image')
                        ->image()
                        ->directory('trainings/images')
                        ->columnSpanFull()
                        ->maxSize(5120)
                        ->placeholder('Click or drag image here')
                        ->helperText('Recommended: 1200x400px (JPEG/PNG, max 5MB)'),

                    FileUpload::make('documents')
                        ->multiple()
                        ->directory('trainings/documents')
                        ->acceptedFileTypes([
                            'application/pdf',
                            'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        ])
                        ->columnSpanFull()
                        ->placeholder('Click or drag documents here')
                        ->helperText('Accepted formats: PDF, DOC, DOCX'),
                ]),
        ]);
    }
}
