<?php

namespace App\Filament\Admin\Resources\Doctors\Pages;

use App\Filament\Admin\Resources\Doctors\DoctorResource;
use App\Repositories\ScheduleRepository;
use AymanAlhattami\FilamentPageWithSidebar\Traits\HasPageSidebar;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
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

class ManageDoctorAvailability extends Page implements HasTable
{
    use InteractsWithRecord;
    use InteractsWithTable;
    use HasPageSidebar;

    protected static string $resource = DoctorResource::class;

    protected string $view = 'panels.admin.resources.doctors.pages.manage-doctor-availability';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('panels/admin/resources/doctor.schedule.availability.title'))
            ->query(
                app(ScheduleRepository::class)->availabilityForDoctor($this->record)
            )
            ->defaultSort('start_date', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label(__('panels/admin/resources/doctor.schedule.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->placeholder('—'),

                TextColumn::make('frequency_config')
                    ->label(__('panels/admin/resources/doctor.schedule.days_of_week'))
                    ->formatStateUsing(function ($state, $record) {
                        $config = $record->frequency_config;
                        $days   = is_array($config) ? ($config['days'] ?? []) : ($config?->days ?? []);
                        $labels = array_map(
                            fn($d) => __('panels/admin/resources/doctor.schedule.days.' . strtolower($d)),
                            $days
                        );
                        return implode(', ', $labels) ?: '—';
                    }),

                TextColumn::make('time_range')
                    ->label(__('panels/admin/resources/doctor.schedule.time_range'))
                    ->state(function ($record) {
                        $p = $record->periods()->first();
                        if (! $p) {
                            return '—';
                        }
                        return $p->start_time . ' → ' . $p->end_time;
                    }),

                TextColumn::make('start_date')
                    ->label(__('panels/admin/resources/doctor.schedule.date_range'))
                    ->formatStateUsing(function ($state, $record) {
                        $from = $record->start_date?->format('d/m/Y') ?? '—';
                        $to   = $record->end_date?->format('d/m/Y')   ?? __('panels/admin/resources/doctor.schedule.ongoing');
                        return "{$from} → {$to}";
                    })
                    ->sortable(),

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
                    ->label(__('panels/admin/resources/doctor.schedule.availability.create'))
                    ->icon('heroicon-o-plus')
                    ->form($this->availabilityForm())
                    ->action(function (array $data) {
                        app(ScheduleRepository::class)->createAvailability(
                            $this->record,
                            $data['name'],
                            $data['days_of_week'],
                            $data['start_time'],
                            $data['end_time'],
                            $data['effective_from'],
                            $data['effective_to'] ?? null,
                            $data['description']  ?? null,
                            $data['is_active'] ?? true
                        );
                        Notification::make()
                            ->success()
                            ->title(__('panels/admin/resources/doctor.schedule.availability.created'))
                            ->send();
                    }),
            ])
            ->actions([
                EditAction::make()
                    ->label(__('panels/admin/resources/doctor.schedule.edit'))
                    ->icon('heroicon-o-pencil-square')
                    ->form($this->availabilityForm())
                    ->fillForm(fn($record) => app(ScheduleRepository::class)->availabilityFormData($record))
                    ->action(function ($record, array $data) {
                        app(ScheduleRepository::class)->updateAvailability(
                            $record,
                            $data['name'],
                            $data['days_of_week'],
                            $data['start_time'],
                            $data['end_time'],
                            $data['effective_from'],
                            $data['effective_to'] ?? null,
                            $data['description']  ?? null,
                            $data['is_active'] ?? true
                        );
                        Notification::make()
                            ->success()
                            ->title(__('panels/admin/resources/doctor.schedule.availability.updated'))
                            ->send();
                    }),

                DeleteAction::make()
                    ->label(__('panels/admin/resources/doctor.schedule.delete'))
                    ->action(fn($record) => app(ScheduleRepository::class)->delete($record))
                    ->successNotificationTitle(__('panels/admin/resources/doctor.schedule.availability.deleted')),
            ])
            ->emptyStateHeading(__('panels/admin/resources/doctor.schedule.availability.empty'))
            ->emptyStateDescription(__('panels/admin/resources/doctor.schedule.availability.empty_desc'))
            ->emptyStateIcon('heroicon-o-clock');
    }

    // ─── Form schema ──────────────────────────────────────────────────────────

    private function availabilityForm(): array
    {
        $settings = app(PlatformSettings::class);

        return [
            TextInput::make('name')
                ->label(__('panels/admin/resources/doctor.schedule.name'))
                ->required()
                ->placeholder(__('panels/admin/resources/doctor.schedule.availability.name_placeholder')),

            CheckboxList::make('days_of_week')
                ->label(__('panels/admin/resources/doctor.schedule.days_of_week'))
                ->required()
                ->options([
                    0 => __('panels/admin/resources/doctor.schedule.days.sunday'),
                    1 => __('panels/admin/resources/doctor.schedule.days.monday'),
                    2 => __('panels/admin/resources/doctor.schedule.days.tuesday'),
                    3 => __('panels/admin/resources/doctor.schedule.days.wednesday'),
                    4 => __('panels/admin/resources/doctor.schedule.days.thursday'),
                    5 => __('panels/admin/resources/doctor.schedule.days.friday'),
                    6 => __('panels/admin/resources/doctor.schedule.days.saturday'),
                ])
                ->columns(2),

            TimePicker::make('start_time')
                ->label(__('panels/admin/resources/doctor.schedule.start_time'))
                ->required()
                ->seconds(false)
                ->native(false)
                ->default($settings->start_time ?? '09:00'),

            TimePicker::make('end_time')
                ->label(__('panels/admin/resources/doctor.schedule.end_time'))
                ->required()
                ->seconds(false)
                ->native(false)
                ->default($settings->end_time ?? '17:00'),

            DatePicker::make('effective_from')
                ->label(__('panels/admin/resources/doctor.schedule.effective_from'))
                ->required()
                ->native(false),

            DatePicker::make('effective_to')
                ->label(__('panels/admin/resources/doctor.schedule.effective_to'))
                ->nullable()
                ->native(false)
                ->minDate(fn($get) => $get('effective_from'))
                ->helperText(__('panels/admin/resources/doctor.schedule.effective_to_hint')),

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
