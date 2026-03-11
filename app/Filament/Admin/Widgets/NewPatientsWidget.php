<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Patient;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class NewPatientsWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 2;

    protected static ?string $heading = null;

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        return __('panels/admin/widgets/dashboard.new_patients_heading');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Patient::query()
                    ->orderByDesc('created_at')
                    ->limit(8)
            )
            ->columns([
                TextColumn::make('full_name')
                    ->label(__('panels/admin/widgets/dashboard.patient'))
                    ->searchable(['first_name', 'last_name']),

                TextColumn::make('phone')
                    ->label(__('panels/admin/widgets/dashboard.phone'))
                    ->placeholder('—'),

                TextColumn::make('created_at')
                    ->label(__('panels/admin/widgets/dashboard.registered'))
                    ->since()
                    ->sortable(),
            ])
            ->recordUrl(fn(Patient $record) => route('filament.admin.resources.patients.view', $record))
            ->paginated(false)
            ->striped();
    }
}
