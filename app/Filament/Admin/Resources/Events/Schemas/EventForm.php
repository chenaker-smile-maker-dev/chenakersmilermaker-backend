<?php

namespace App\Filament\Admin\Resources\Events\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use AbdulmajeedJamaan\FilamentTranslatableTabs\TranslatableTabs;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TranslatableTabs::make('translatable_data')
                ->columnSpanFull()
                ->schema([
                    TextInput::make('title')
                        ->required()
                        ->placeholder(__('panels/admin/resources/event.enter_event_title'))
                        ->maxLength(255)
                        ->columnSpanFull(),
                    RichEditor::make('description')
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
                        ->columnSpanFull()
                        ->placeholder(__('panels/admin/resources/event.enter_event_location'))
                        ->maxLength(255),
                ]),

            Section::make(__('panels/admin/resources/event.event_metadata'))
                ->columns(2)
                ->schema([
                    DatePicker::make('date')
                        ->required()
                        ->placeholder(__('panels/admin/resources/event.select_event_date'))
                        ->native(false),
                    Toggle::make('is_archived')
                        ->label(__('panels/admin/resources/event.archived'))
                        ->inline(),
                ]),
        ]);
    }
}
