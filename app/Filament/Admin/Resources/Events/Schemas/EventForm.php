<?php

namespace App\Filament\Admin\Resources\Events\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use AbdulmajeedJamaan\FilamentTranslatableTabs\TranslatableTabs;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TranslatableTabs::make('translatable_data')
                ->columnSpanFull()
                ->schema([
                    TextInput::make('title')
                        ->label(__('panels/admin/resources/event.title'))
                        ->required()
                        ->placeholder(__('panels/admin/resources/event.enter_event_title'))
                        ->maxLength(255)
                        ->columnSpanFull(),
                    RichEditor::make('description')
                        ->label(__('panels/admin/resources/event.description'))
                        ->columnSpanFull()
                        ->placeholder(__('panels/admin/resources/event.describe_event_details'))
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
                    TextInput::make('location')
                        ->label(__('panels/admin/resources/event.location'))
                        ->columnSpanFull()
                        ->placeholder(__('panels/admin/resources/event.enter_event_location'))
                        ->maxLength(255),
                    RichEditor::make('speakers')
                        ->label('Speakers')
                        ->columnSpanFull()
                        ->disableAllToolbarButtons()
                        ->toolbarButtons(['bold', 'italic', 'bulletList', 'redo', 'undo']),
                    RichEditor::make('about_event')
                        ->label('About the Event')
                        ->columnSpanFull()
                        ->disableAllToolbarButtons()
                        ->toolbarButtons(['bold', 'italic', 'bulletList', 'orderedList', 'redo', 'undo']),
                    RichEditor::make('what_to_expect')
                        ->label('What to Expect')
                        ->columnSpanFull()
                        ->disableAllToolbarButtons()
                        ->toolbarButtons(['bold', 'italic', 'bulletList', 'orderedList', 'redo', 'undo']),
                ]),

            Section::make(__('panels/admin/resources/event.event_metadata'))
                ->columns(2)
                ->schema([
                    DatePicker::make('date')
                        ->label(__('panels/admin/resources/event.event_date'))
                        ->required()
                        ->placeholder(__('panels/admin/resources/event.select_event_date'))
                        ->native(false),
                    TimePicker::make('time')
                        ->label('Event Time')
                        ->seconds(false)
                        ->native(false),
                    Toggle::make('is_archived')
                        ->label(__('panels/admin/resources/event.archived'))
                        ->inline()
                        ->columnSpanFull(),
                ]),

            Section::make('Gallery')
                ->schema([
                    SpatieMediaLibraryFileUpload::make('gallery')
                        ->label('Event Photos')
                        ->collection('gallery')
                        ->multiple()
                        ->reorderable()
                        ->image()
                        ->maxSize(5120)
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
