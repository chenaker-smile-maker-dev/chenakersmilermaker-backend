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
                        ->placeholder('Enter event title')
                        ->maxLength(255)
                        ->columnSpanFull(),
                    RichEditor::make('description')
                        ->columnSpanFull()
                        ->placeholder('Describe the event details')
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
                        ->placeholder('Enter event location (e.g., Clinic Name, Address)')
                        ->maxLength(255),
                ]),

            Section::make('Event Metadata')
                ->columns(2)
                ->schema([
                    DatePicker::make('date')
                        ->required()
                        ->placeholder('Select event date')
                        ->native(false),
                    Toggle::make('is_archived')
                        ->label('Archived')
                        ->inline(),
                ]),
        ]);
    }
}
