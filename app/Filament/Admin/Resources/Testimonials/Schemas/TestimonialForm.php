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
            Section::make(__('panels/admin/resources/testimonial.patient_information'))
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    Select::make('patient_id')
                        ->label(__('panels/admin/resources/testimonial.patient_optional'))
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
                        ->helperText(__('panels/admin/resources/testimonial.select_existing_or_enter_custom')),

                    TextInput::make('patient_name')
                        ->label(__('panels/admin/resources/testimonial.patient_name_display'))
                        ->required()
                        ->placeholder(__('panels/admin/resources/testimonial.name_to_display_in_testimonial'))
                        ->maxLength(255),

                    Select::make('rating')
                        ->required()
                        ->options([
                            1 => __('panels/admin/resources/testimonial.rating_options.1'),
                            2 => __('panels/admin/resources/testimonial.rating_options.2'),
                            3 => __('panels/admin/resources/testimonial.rating_options.3'),
                            4 => __('panels/admin/resources/testimonial.rating_options.4'),
                            5 => __('panels/admin/resources/testimonial.rating_options.5'),
                        ])
                        ->native(false),

                    Toggle::make('is_published')
                        ->label(__('panels/admin/resources/testimonial.published'))
                        ->helperText(__('panels/admin/resources/testimonial.publish_helper'))
                        ->inline(false),
                ]),

            Section::make(__('panels/admin/resources/testimonial.testimonial_content'))
                ->columnSpanFull()
                ->columns(1)
                ->schema([
                    RichEditor::make('content')
                        ->required()
                        ->placeholder(__('panels/admin/resources/testimonial.write_review_placeholder'))
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
