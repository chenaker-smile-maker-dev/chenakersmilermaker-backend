<?php

namespace App\Filament\Admin\Resources\Doctors\Pages;

use App\Filament\Admin\Resources\Doctors\DoctorResource;
use App\Repositories\ScheduleRepository;
use AymanAlhattami\FilamentPageWithSidebar\Traits\HasPageSidebar;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use App\Settings\PlatformSettings;

class ManageDoctorBlocked extends Page implements HasTable
{
    use InteractsWithRecord;
    use InteractsWithTable;
    use HasPageSidebar;

    protected static string $resource = DoctorResource::class;

    protected string $view = 'panels.admin.resources.doctors.pages.manage-doctor-blocked';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('panels/admin/resources/doctor.schedule.blocked.title'))
            ->query(
                app(ScheduleRepository::class)->blockedForDoctor($this->record)
            )
            ->defaultSort('start_date', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label(__('panels/admin/resources/doctor.schedule.blocked.reason'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->placeholder('—'),

                TextColumn::make('start_date')
                    ->label(__('panels/admin/resources/doctor.schedule.date_range'))
                    ->formatStateUsing(function ($state, $record) {
                        $from = $record->start_date?->format('d/m/Y') ?? '—';
                        $to   = $record->end_date?->format('d/m/Y')   ?? '—';
                        return "{$from} → {$to}";
                    })
                    ->sortable(),

                TextColumn::make('time_range')
                    ->label(__('panels/admin/resources/doctor.schedule.time_range'))
                    ->state(function ($record) {
                        $p = $record->periods()->first();
                        if (! $p) {
                            return '—';
                        }
                        $allDay = $p->start_time === '00:00' && $p->end_time === '23:59';
                        if ($allDay) {
                            return __('panels/admin/resources/doctor.schedule.blocked.all_day');
                        }
                        return $p->start_time . ' → ' . $p->end_time;
                    }),

                IconColumn::make('is_active')
                    ->label(__('panels/admin/resources/doctor.schedule.active'))
                    ->boolean()
                    ->sortable(),

                TextColumn::make('description')
                    ->label(__('panels/admin/resources/doctor.schedule.description'))
                    ->limit(40)
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label(__('panels/admin/resources/doctor.schedule.blocked.create'))
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->form($this->blockedForm())
                    ->action(function (array $data) {
                        app(ScheduleRepository::class)->createBlocked(
                            $this->record,
                            $data['reason'],
                            $data['start_date'],
                            $data['end_date'],
                            $data['has_time_restriction'] ? ($data['block_start_time'] ?? null) : null,
                            $data['has_time_restriction'] ? ($data['block_end_time']   ?? null) : null,
                            $data['description'] ?? null,
                            $data['is_active']   ?? true
                        );
                        Notification::make()
                            ->success()
                            ->title(__('panels/admin/resources/doctor.schedule.blocked.created'))
                            ->send();
                    }),
            ])
            ->actions([
                EditAction::make()
                    ->label(__('panels/admin/resources/doctor.schedule.edit'))
                    ->icon('heroicon-o-pencil-square')
                    ->form($this->blockedForm())
                    ->fillForm(fn($record) => app(ScheduleRepository::class)->blockedFormData($record))
                    ->action(function ($record, array $data) {
                        app(ScheduleRepository::class)->updateBlocked(
                            $record,
                            $data['reason'],
                            $data['start_date'],
                            $data['end_date'],
                            $data['has_time_restriction'] ? ($data['block_start_time'] ?? null) : null,
                            $data['has_time_restriction'] ? ($data['block_end_time']   ?? null) : null,
                            $data['description'] ?? null,
                            $data['is_active']   ?? true
                        );
                        Notification::make()
                            ->success()
                            ->title(__('panels/admin/resources/doctor.schedule.blocked.updated'))
                            ->send();
                    }),

                DeleteAction::make()
                    ->label(__('panels/admin/resources/doctor.schedule.delete'))
                    ->action(fn($record) => app(ScheduleRepository::class)->delete($record))
                    ->successNotificationTitle(__('panels/admin/resources/doctor.schedule.blocked.deleted')),
            ])
            ->emptyStateHeading(__('panels/admin/resources/doctor.schedule.blocked.empty'))
            ->emptyStateDescription(__('panels/admin/resources/doctor.schedule.blocked.empty_desc'))
            ->emptyStateIcon('heroicon-o-no-symbol');
    }

    // ─── Form schema ──────────────────────────────────────────────────────────

    private function blockedForm(): array
    {
        $settings = app(PlatformSettings::class);

        return [
            TextInput::make('reason')
                ->label(__('panels/admin/resources/doctor.schedule.blocked.reason'))
                ->required()
                ->placeholder(__('panels/admin/resources/doctor.schedule.blocked.reason_placeholder')),

            DatePicker::make('start_date')
                ->label(__('panels/admin/resources/doctor.schedule.from_date'))
                ->required()
                ->native(false),

            DatePicker::make('end_date')
                ->label(__('panels/admin/resources/doctor.schedule.to_date'))
                ->required()
                ->native(false)
                ->minDate(fn($get) => $get('start_date')),

            Toggle::make('has_time_restriction')
                ->label(__('panels/admin/resources/doctor.schedule.blocked.specific_hours'))
                ->live()
                ->default(false),

            TimePicker::make('block_start_time')
                ->label(__('panels/admin/resources/doctor.schedule.start_time'))
                ->seconds(false)
                ->native(false)
                ->default($settings->start_time ?? '09:00')
                ->visible(fn($get) => (bool) $get('has_time_restriction'))
                ->required(fn($get) => (bool) $get('has_time_restriction')),

            TimePicker::make('block_end_time')
                ->label(__('panels/admin/resources/doctor.schedule.end_time'))
                ->seconds(false)
                ->native(false)
                ->default($settings->end_time ?? '17:00')
                ->visible(fn($get) => (bool) $get('has_time_restriction'))
                ->required(fn($get) => (bool) $get('has_time_restriction')),

            Textarea::make('description')
                ->label(__('panels/admin/resources/doctor.schedule.description'))
                ->nullable()
                ->rows(2),

            Toggle::make('is_active')
                ->label(__('panels/admin/resources/doctor.schedule.active'))
                ->default(true),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
