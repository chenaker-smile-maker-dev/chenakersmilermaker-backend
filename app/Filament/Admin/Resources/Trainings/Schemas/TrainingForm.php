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
                        ->placeholder(__('panels/admin/resources/training.enter_training_title'))
                        ->maxLength(255)
                        ->columnSpanFull(),
                    RichEditor::make('description')
                        ->columnSpanFull()
                        ->placeholder(__('panels/admin/resources/training.describe_training_content'))
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

            Section::make(__('panels/admin/resources/training.training_information'))
                ->columns(2)
                ->schema([
                    TextInput::make('trainer_name')
                        ->required()
                        ->placeholder(__('panels/admin/resources/training.full_name_of_instructor'))
                        ->maxLength(255),
                    TextInput::make('duration')
                        ->required()
                        ->placeholder(__('panels/admin/resources/training.duration_placeholder'))
                        ->maxLength(255),
                    TextInput::make('video_url')
                        ->url()
                        ->columnSpanFull()
                        ->placeholder(__('panels/admin/resources/training.video_url_placeholder'))
                        ->helperText(__('panels/admin/resources/training.training_video_helper')),
                ]),

            Section::make(__('panels/admin/resources/training.media'))
                ->columns(1)
                ->schema([
                    FileUpload::make('image')
                        ->image()
                        ->directory('trainings/images')
                        ->columnSpanFull()
                        ->maxSize(5120)
                        ->placeholder(__('panels/admin/resources/training.click_or_drag_image_here'))
                        ->helperText(__('panels/admin/resources/training.image_helper')),

                        FileUpload::make('documents')
                        ->multiple()
                        ->directory('trainings/documents')
                        ->acceptedFileTypes([
                            'application/pdf',
                            'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        ])
                        ->columnSpanFull()
                            ->placeholder(__('panels/admin/resources/training.click_or_drag_documents_here'))
                            ->helperText(__('panels/admin/resources/training.documents_helper')),
                ]),
        ]);
    }
}
