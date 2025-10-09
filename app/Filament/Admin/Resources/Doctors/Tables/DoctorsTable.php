<?php

namespace App\Filament\Admin\Resources\Doctors\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class DoctorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Split::make([
                    ImageColumn::make('thumb_image')
                        ->circular()
                        ->size(80)
                        ->grow(false),

                    Stack::make([
                        TextColumn::make("name")
                            ->weight(FontWeight::Bold)
                            ->searchable()
                            ->sortable(),

                        TextColumn::make("specialty")
                            ->color('gray')
                            ->limit(50)
                            ->wrap(),

                        // TextColumn::make("diplomas_count")
                        //     ->badge()
                        //     ->color("primary")
                        //     ->label('Diplomas'),
                    ])->space(1),


                ])->from(''),
            ])
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
