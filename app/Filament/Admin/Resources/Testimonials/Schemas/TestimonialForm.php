<?php

namespace App\Filament\Admin\Resources\Testimonials\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TestimonialForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Patient Information')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    Select::make('patient_id')
                        ->label('Patient (Optional)')
                        ->relationship('patient', 'first_name')
                        ->searchable(['first_name', 'last_name'])
                        ->getOptionLabelFromRecordUsing(fn($record) => $record->full_name)
                        ->preload()
                        ->live()
                        ->afterStateUpdated(function ($state, callable $set) {
                            if ($state) {
                                $patient = \App\Models\Patient::find($state);
                                if ($patient) {
                                    $set('patient_name', $patient->full_name);
                                }
                            }
                        })
                        ->helperText('Select an existing patient or enter a custom name below'),

                    TextInput::make('patient_name')
                        ->label('Patient Name / Display Name')
                        ->required()
                        ->placeholder('Name to display in testimonial')
                        ->maxLength(255),

                    Select::make('rating')
                        ->required()
                        ->options([
                            1 => '⭐ 1 Star - Poor',
                            2 => '⭐⭐ 2 Stars - Fair',
                            3 => '⭐⭐⭐ 3 Stars - Good',
                            4 => '⭐⭐⭐⭐ 4 Stars - Very Good',
                            5 => '⭐⭐⭐⭐⭐ 5 Stars - Excellent',
                        ])
                        ->native(false),

                    Toggle::make('is_published')
                        ->label('Published')
                        ->helperText('Publish this testimonial on the website')
                        ->inline(false),
                ]),

            Section::make('Testimonial Content')
                ->columnSpanFull()
                ->columns(1)
                ->schema([
                    RichEditor::make('content')
                        ->required()
                        ->placeholder('Write the patient\'s review and feedback')
                        ->extraAttributes(['style' => 'min-height: 400px;'])
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
        ]);
    }
}
