<?php

namespace App\Filament\Admin\Resources\Events\Schemas;

use AbdulmajeedJamaan\FilamentTranslatableTabs\TranslatableTabs;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
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
                            'attachFiles', 'blockquote', 'bold', 'bulletList',
                            'codeBlock', 'italic', 'link', 'orderedList',
                            'redo', 'strike', 'undo',
                        ]),
                    TextInput::make('location')
                        ->label(__('panels/admin/resources/event.location'))
                        ->columnSpanFull()
                        ->placeholder(__('panels/admin/resources/event.enter_event_location'))
                        ->maxLength(255),
                    RichEditor::make('speakers')
                        ->label('Speakers')
                        ->columnSpanFull()
                        ->placeholder('Enter speakers information...')
                        ->disableAllToolbarButtons()
                        ->toolbarButtons(['bold', 'italic', 'bulletList', 'orderedList', 'undo', 'redo']),
                    RichEditor::make('about_event')
                        ->label('About Event')
                        ->columnSpanFull()
                        ->placeholder('Describe the event...')
                        ->disableAllToolbarButtons()
                        ->toolbarButtons(['bold', 'italic', 'bulletList', 'orderedList', 'undo', 'redo']),
                    RichEditor::make('what_to_expect')
                        ->label('What to Expect')
                        ->columnSpanFull()
                        ->placeholder('What attendees can expect...')
                        ->disableAllToolbarButtons()
                        ->toolbarButtons(['bold', 'italic', 'bulletList', 'orderedList', 'undo', 'redo']),
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
                        ->placeholder('Select time')
                        ->native(false),
                    Toggle::make('is_archived')
                        ->label(__('panels/admin/resources/event.archived'))
                        ->inline(),
                ]),

            Section::make('Gallery')
                ->schema([
                    SpatieMediaLibraryFileUpload::make('gallery')
                        ->label('Event Pictures')
                        ->collection('gallery')
                        ->multiple()
                        ->image()
                        ->maxSize(5120)
                        ->columnSpanFull(),
                ]),
        ]);
    }
}

