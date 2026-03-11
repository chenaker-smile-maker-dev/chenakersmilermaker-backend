<?php

namespace App\Filament\Admin\Resources\Doctors\Widgets;

use App\Enums\Appointment\AppointmentStatus;
use App\Filament\Admin\Resources\Appointments\AppointmentResource;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Repositories\ScheduleRepository;
use Carbon\Carbon;
use Carbon\WeekDay;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Guava\Calendar\Enums\CalendarViewType;
use Guava\Calendar\Filament\Actions\CreateAction;
use Guava\Calendar\Filament\Actions\EditAction;
use Guava\Calendar\Filament\Actions\ViewAction;
use Guava\Calendar\Filament\CalendarWidget;
use Guava\Calendar\ValueObjects\CalendarEvent;
use Guava\Calendar\ValueObjects\DateClickInfo;
use Guava\Calendar\ValueObjects\DateSelectInfo;
use Guava\Calendar\ValueObjects\EventDropInfo;
use Guava\Calendar\ValueObjects\EventResizeInfo;
use Guava\Calendar\ValueObjects\FetchInfo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Zap\Enums\ScheduleTypes;

class DoctorCalendarWidget extends CalendarWidget
{
    protected int|string|array $columnSpan = 'full';
    protected CalendarViewType $calendarView = CalendarViewType::TimeGridWeek;
    protected WeekDay $firstDay = WeekDay::Monday;
    protected bool $dayMaxEvents = true;
    protected string $view = 'panels.admin.resources.doctors.widgets.doctor-calendar-widget';

    // Interactivity
    protected bool $dateClickEnabled = true;
    protected bool $dateSelectEnabled = true;
    protected bool $eventClickEnabled = true;
    protected bool $eventDragEnabled = true;
    protected bool $eventResizeEnabled = true;

    /** @var int The doctor record ID bound from the parent page */
    public int $doctorId;

    /** @var array<string> Status filter (multiple) */
    public array $filterStatuses = [];

    /** @var bool Toggle availability background slots */
    public bool $showAvailability = true;

    /** @var bool Toggle blocked background slots */
    public bool $showBlocked = true;

    /** @var string Active calendar view type */
    public string $activeView = CalendarViewType::TimeGridWeek->value;

    protected function getDoctor(): Doctor
    {
        return Doctor::findOrFail($this->doctorId);
    }

    public function getHeading(): string
    {
        return __('panels/admin/resources/doctor.calendar.heading');
    }

    public function getCalendarView(): CalendarViewType
    {
        return CalendarViewType::from($this->activeView);
    }

    public function getOptions(): array
    {
        $locale = app()->getLocale();
        $isRtl  = $locale === 'ar';

        return array_filter([
            'buttonText' => [
                'today' => __('panels/admin/widgets/dashboard.calendar_today'),
            ],
            'direction' => $isRtl ? 'rtl' : null,
        ]);
    }

    public function switchView(string $view): void
    {
        $this->activeView = $view;
        $this->setOption('view', $view);
    }

    // ─── Override to handle background events (no model) gracefully ───────────

    protected function resolveEventRecord(): ?Model
    {
        $model = $this->getRawCalendarContextData('event.extendedProps.model');
        $key = $this->getRawCalendarContextData('event.extendedProps.key');

        // Background events (availability/blocked) have no model/key — skip
        if (! $model || ! $key) {
            $this->eventRecord = null;

            return null;
        }

        return parent::resolveEventRecord();
    }

    // ─── Events ───────────────────────────────────────────────────────────────

    protected function getEvents(FetchInfo $info): Collection|array|Builder
    {
        $events = [];
        $doctor = $this->getDoctor();

        // ── Appointments ──────────────────────────────────────────────────────
        $appointmentQuery = Appointment::query()
            ->with(['service', 'patient'])
            ->where('doctor_id', $doctor->id)
            ->whereDate('from', '>=', $info->start)
            ->whereDate('to', '<=', $info->end);

        if (! empty($this->filterStatuses)) {
            $appointmentQuery->whereIn('status', $this->filterStatuses);
        }

        foreach ($appointmentQuery->get() as $appointment) {
            $events[] = $appointment;
        }

        // ── Schedule periods (availability & blocked) ─────────────────────────
        $schedules = $doctor->schedules()
            ->with('periods')
            ->whereIn('schedule_type', [
                ScheduleTypes::AVAILABILITY->value,
                ScheduleTypes::BLOCKED->value,
            ])
            ->where('is_active', true)
            ->where('start_date', '<=', $info->end)
            ->where(fn($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', $info->start))
            ->get();

        foreach ($schedules as $schedule) {
            $isBlocked = $schedule->schedule_type === ScheduleTypes::BLOCKED
                || $schedule->schedule_type->value === ScheduleTypes::BLOCKED->value;

            if ($isBlocked && ! $this->showBlocked) {
                continue;
            }
            if (! $isBlocked && ! $this->showAvailability) {
                continue;
            }

            $period = $schedule->periods->first();

            if ($isBlocked) {
                // ── Blocked: one spanning background event ───────────────────
                $startTime = $period?->start_time ?? '00:00';
                $endTime   = $period?->end_time   ?? '23:59';
                $isAllDay  = ($startTime === '00:00' && $endTime === '23:59');

                $startDate = $schedule->start_date->format('Y-m-d');
                $endCarbon = $schedule->end_date ?? $schedule->start_date;

                if ($isAllDay) {
                    $events[] = CalendarEvent::make()
                        ->title($schedule->name ?: __('panels/admin/resources/doctor.calendar.blocked'))
                        ->start($startDate)
                        ->end($endCarbon->copy()->addDay()->format('Y-m-d'))
                        ->allDay(true)
                        ->backgroundColor('#EF444425')
                        ->textColor('#EF4444')
                        ->displayBackground()
                        ->editable(false);
                } else {
                    $events[] = CalendarEvent::make()
                        ->title($schedule->name ?: __('panels/admin/resources/doctor.calendar.blocked'))
                        ->start($startDate . 'T' . $startTime)
                        ->end($endCarbon->format('Y-m-d') . 'T' . $endTime)
                        ->backgroundColor('#EF444425')
                        ->textColor('#EF4444')
                        ->displayBackground()
                        ->editable(false);
                }
            } else {
                // ── Availability: per-day instances for the calendar range ────
                $config    = $schedule->frequency_config;
                $days      = $config?->days ?? [];
                $startTime = $period?->start_time ?? '09:00';
                $endTime   = $period?->end_time   ?? '17:00';

                $rangeStart = Carbon::parse(
                    max($schedule->start_date->format('Y-m-d'), $info->start->format('Y-m-d'))
                );
                $rangeEnd = Carbon::parse(
                    min(
                        $schedule->end_date?->format('Y-m-d') ?? $info->end->format('Y-m-d'),
                        $info->end->format('Y-m-d')
                    )
                );

                $current = $rangeStart->copy();
                while ($current->lte($rangeEnd)) {
                    if (in_array(strtolower($current->format('l')), $days)) {
                        $events[] = CalendarEvent::make()
                            ->title($schedule->name ?: __('panels/admin/resources/doctor.calendar.availability'))
                            ->start($current->format('Y-m-d') . 'T' . $startTime)
                            ->end($current->format('Y-m-d') . 'T' . $endTime)
                            ->backgroundColor('#10B98125')
                            ->textColor('#059669')
                            ->displayBackground()
                            ->editable(false);
                    }
                    $current->addDay();
                }
            }
        }

        return $events;
    }

    // ─── Actions ──────────────────────────────────────────────────────────────

    public function createAppointmentAction(): CreateAction
    {
        return $this->createAction(Appointment::class)
            ->label(__('panels/admin/widgets/dashboard.calendar_create_appointment'))
            ->mountUsing(function (Schema $schema) {
                $raw      = $this->getRawCalendarContextData() ?? $this->getMountedAction()?->getArguments() ?? [];
                $data     = $raw['data'] ?? [];
                $tzOffset = (int) ($data['tzOffset'] ?? 0);
                $startRaw = $data['start'] ?? $data['date'] ?? null;
                $endRaw   = $data['end'] ?? null;
                $start    = $startRaw ? Carbon::parse($startRaw)->utcOffset($tzOffset) : now();
                $end      = $endRaw ? Carbon::parse($endRaw)->utcOffset($tzOffset) : (clone $start)->addHour();
                $schema->fill([
                    'doctor_id' => $this->doctorId,
                    'from'      => $start->toDateTimeString(),
                    'to'        => $end->toDateTimeString(),
                    'status'    => AppointmentStatus::PENDING->value,
                ]);
            });
    }

    public function createAvailabilityAction(): Action
    {
        return Action::make('createAvailability')
            ->label(__('panels/admin/widgets/dashboard.calendar_create_availability'))
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->schema([
                TextInput::make('name')
                    ->label(__('panels/admin/resources/doctor.schedule.name'))
                    ->required(),
                CheckboxList::make('days_of_week')
                    ->label(__('panels/admin/resources/doctor.schedule.days_of_week'))
                    ->options([
                        0 => __('panels/admin/resources/doctor.schedule.days.sunday'),
                        1 => __('panels/admin/resources/doctor.schedule.days.monday'),
                        2 => __('panels/admin/resources/doctor.schedule.days.tuesday'),
                        3 => __('panels/admin/resources/doctor.schedule.days.wednesday'),
                        4 => __('panels/admin/resources/doctor.schedule.days.thursday'),
                        5 => __('panels/admin/resources/doctor.schedule.days.friday'),
                        6 => __('panels/admin/resources/doctor.schedule.days.saturday'),
                    ])
                    ->columns(2)
                    ->required(),
                TimePicker::make('start_time')
                    ->label(__('panels/admin/resources/doctor.schedule.start_time'))
                    ->required()
                    ->seconds(false)
                    ->native(false),
                TimePicker::make('end_time')
                    ->label(__('panels/admin/resources/doctor.schedule.end_time'))
                    ->required()
                    ->seconds(false)
                    ->native(false),
                DatePicker::make('effective_from')
                    ->label(__('panels/admin/resources/doctor.schedule.effective_from'))
                    ->required()
                    ->native(false),
            ])
            ->mountUsing(function (Schema $schema) {
                $raw      = $this->getRawCalendarContextData() ?? $this->getMountedAction()?->getArguments() ?? [];
                $data     = $raw['data'] ?? [];
                $tzOffset = (int) ($data['tzOffset'] ?? 0);
                $startRaw = $data['start'] ?? $data['date'] ?? null;
                $endRaw   = $data['end'] ?? null;
                $start    = $startRaw ? Carbon::parse($startRaw)->utcOffset($tzOffset) : now();
                $end      = $endRaw ? Carbon::parse($endRaw)->utcOffset($tzOffset) : (clone $start)->addHour();
                $schema->fill([
                    'days_of_week'   => [$start->dayOfWeek],
                    'start_time'     => $start->format('H:i'),
                    'end_time'       => $end->format('H:i'),
                    'effective_from' => $start->format('Y-m-d'),
                ]);
            })
            ->action(function (array $data) {
                app(ScheduleRepository::class)->createAvailability(
                    $this->getDoctor(),
                    $data['name'],
                    $data['days_of_week'],
                    $data['start_time'],
                    $data['end_time'],
                    $data['effective_from'],
                );
                $this->refreshRecords();
                Notification::make()->success()
                    ->title(__('panels/admin/widgets/dashboard.calendar_availability_created'))
                    ->send();
            });
    }

    public function createBlockedAction(): Action
    {
        return Action::make('createBlocked')
            ->label(__('panels/admin/widgets/dashboard.calendar_create_blocked'))
            ->icon('heroicon-o-no-symbol')
            ->color('danger')
            ->schema([
                TextInput::make('reason')
                    ->label(__('panels/admin/resources/doctor.schedule.blocked.reason'))
                    ->required(),
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
                    ->visible(fn($get) => (bool) $get('has_time_restriction'))
                    ->required(fn($get) => (bool) $get('has_time_restriction')),
                TimePicker::make('block_end_time')
                    ->label(__('panels/admin/resources/doctor.schedule.end_time'))
                    ->seconds(false)
                    ->native(false)
                    ->visible(fn($get) => (bool) $get('has_time_restriction'))
                    ->required(fn($get) => (bool) $get('has_time_restriction')),
            ])
            ->mountUsing(function (Schema $schema) {
                $raw      = $this->getRawCalendarContextData() ?? $this->getMountedAction()?->getArguments() ?? [];
                $data     = $raw['data'] ?? [];
                $tzOffset = (int) ($data['tzOffset'] ?? 0);
                $startRaw = $data['start'] ?? $data['date'] ?? null;
                $endRaw   = $data['end'] ?? null;
                $start    = $startRaw ? Carbon::parse($startRaw)->utcOffset($tzOffset) : now();
                $end      = $endRaw ? Carbon::parse($endRaw)->utcOffset($tzOffset) : (clone $start);
                $schema->fill([
                    'start_date' => $start->format('Y-m-d'),
                    'end_date'   => $end->format('Y-m-d'),
                ]);
            })
            ->action(function (array $data) {
                app(ScheduleRepository::class)->createBlocked(
                    $this->getDoctor(),
                    $data['reason'],
                    $data['start_date'],
                    $data['end_date'],
                    $data['has_time_restriction'] ? ($data['block_start_time'] ?? null) : null,
                    $data['has_time_restriction'] ? ($data['block_end_time']   ?? null) : null,
                );
                $this->refreshRecords();
                Notification::make()->success()
                    ->title(__('panels/admin/widgets/dashboard.calendar_blocked_created'))
                    ->send();
            });
    }

    public function viewAction(): ViewAction
    {
        return ViewAction::make()
            ->label(__('panels/admin/widgets/dashboard.calendar_view_appointment'))
            ->icon('heroicon-o-eye');
    }

    public function editAction(): EditAction
    {
        return EditAction::make()
            ->label(__('panels/admin/widgets/dashboard.calendar_edit_appointment'))
            ->icon('heroicon-o-pencil-square');
    }

    // ─── Schemas ──────────────────────────────────────────────────────────────

    public function appointmentSchema(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->columns(2)
                ->schema([
                    DateTimePicker::make('from')
                        ->label(__('panels/admin/resources/appointment.from'))
                        ->required(),
                    DateTimePicker::make('to')
                        ->label(__('panels/admin/resources/appointment.to'))
                        ->required(),
                    Select::make('doctor_id')
                        ->label(__('panels/admin/resources/appointment.doctor'))
                        ->relationship('doctor', 'name')
                        ->default($this->doctorId),
                    Select::make('service_id')
                        ->label(__('panels/admin/resources/appointment.service'))
                        ->relationship('service', 'name'),
                    Select::make('patient_id')
                        ->label(__('panels/admin/resources/appointment.patient'))
                        ->searchable()
                        ->preload()
                        ->getSearchResultsUsing(
                            fn(string $search) =>
                            \App\Models\Patient::query()
                                ->where(function ($q) use ($search) {
                                    $q->where('first_name', 'like', "%{$search}%")
                                        ->orWhere('last_name', 'like', "%{$search}%")
                                        ->orWhere('email', 'like', "%{$search}%");
                                })
                                ->limit(50)
                                ->get()
                                ->mapWithKeys(fn($p) => [$p->id => $p->full_name])
                        )
                        ->getOptionLabelUsing(fn($value) => \App\Models\Patient::find($value)?->full_name),
                    TextInput::make('price')
                        ->label(__('panels/admin/resources/appointment.price'))
                        ->numeric()
                        ->suffix('DZD'),
                    // Status is read-only in the calendar edit form.
                    Select::make('status')
                        ->label(__('panels/admin/resources/appointment.status'))
                        ->options(AppointmentStatus::class)
                        ->default(AppointmentStatus::PENDING->value)
                        ->disabled(fn(?Appointment $record) => $record !== null)
                        ->dehydrated(),
                ]),
        ]);
    }

    // ─── Context Menus ────────────────────────────────────────────────────────

    protected function getDateClickContextMenuActions(): array
    {
        return [
            $this->createAppointmentAction(),
            $this->createAvailabilityAction(),
            $this->createBlockedAction(),
        ];
    }

    protected function getDateSelectContextMenuActions(): array
    {
        return [
            $this->createAppointmentAction(),
            $this->createAvailabilityAction(),
            $this->createBlockedAction(),
        ];
    }

    protected function getEventClickContextMenuActions(): array
    {
        return [
            $this->viewAction(),
            $this->editAction(),

            // ── Status transition actions ─────────────────────────────────────
            Action::make('confirm_appointment')
                ->label(__('panels/admin/widgets/dashboard.calendar_confirm'))
                ->icon('heroicon-o-check-circle')
                ->color('info')
                ->visible(fn() => $this->getEventRecord()?->status === AppointmentStatus::PENDING)
                ->requiresConfirmation()
                ->modalHeading(__('panels/admin/widgets/dashboard.calendar_confirm'))
                ->modalDescription(__('panels/admin/widgets/dashboard.calendar_confirm_body'))
                ->action(function () {
                    $this->getEventRecord()->update(['status' => AppointmentStatus::CONFIRMED]);
                    $this->refreshRecords();
                    Notification::make()->success()->title(__('panels/admin/widgets/dashboard.calendar_confirmed_success'))->send();
                }),

            Action::make('reject_appointment')
                ->label(__('panels/admin/widgets/dashboard.calendar_reject'))
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn() => $this->getEventRecord()?->status === AppointmentStatus::PENDING)
                ->requiresConfirmation()
                ->modalHeading(__('panels/admin/widgets/dashboard.calendar_reject'))
                ->modalDescription(__('panels/admin/widgets/dashboard.calendar_reject_body'))
                ->action(function () {
                    $this->getEventRecord()->update(['status' => AppointmentStatus::REJECTED]);
                    $this->refreshRecords();
                    Notification::make()->success()->title(__('panels/admin/widgets/dashboard.calendar_rejected_success'))->send();
                }),

            Action::make('cancel_appointment')
                ->label(__('panels/admin/widgets/dashboard.calendar_cancel'))
                ->icon('heroicon-o-no-symbol')
                ->color('gray')
                ->visible(fn() => in_array($this->getEventRecord()?->status, [AppointmentStatus::PENDING, AppointmentStatus::CONFIRMED]))
                ->requiresConfirmation()
                ->modalHeading(__('panels/admin/widgets/dashboard.calendar_cancel'))
                ->modalDescription(__('panels/admin/widgets/dashboard.calendar_cancel_body'))
                ->action(function () {
                    $this->getEventRecord()->update(['status' => AppointmentStatus::CANCELLED]);
                    $this->refreshRecords();
                    Notification::make()->success()->title(__('panels/admin/widgets/dashboard.calendar_cancelled_success'))->send();
                }),

            Action::make('complete_appointment')
                ->label(__('panels/admin/widgets/dashboard.calendar_complete'))
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->visible(fn() => $this->getEventRecord()?->status === AppointmentStatus::CONFIRMED)
                ->requiresConfirmation()
                ->modalHeading(__('panels/admin/widgets/dashboard.calendar_complete'))
                ->modalDescription(__('panels/admin/widgets/dashboard.calendar_complete_body'))
                ->action(function () {
                    $this->getEventRecord()->update(['status' => AppointmentStatus::COMPLETED]);
                    $this->refreshRecords();
                    Notification::make()->success()->title(__('panels/admin/widgets/dashboard.calendar_completed_success'))->send();
                }),

            // ── Navigation ────────────────────────────────────────────────────
            Action::make('goto_appointment')
                ->label(__('panels/admin/widgets/dashboard.calendar_goto_appointment'))
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->visible(fn() => $this->getEventRecord() !== null)
                ->url(
                    fn() => $this->getEventRecord()
                        ? AppointmentResource::getUrl('view', ['record' => $this->getEventRecord()->getKey()])
                        : null
                ),
        ];
    }

    // ─── Drag & Drop ──────────────────────────────────────────────────────────

    protected function onEventDrop(EventDropInfo $info, Model $event): bool
    {
        if (! $event instanceof Appointment) {
            return false;
        }

        $newStart = $info->event->getStart();
        $newEnd   = $info->event->getEnd();

        if (! $newStart) {
            return false;
        }

        $duration = $event->from->diffInMinutes($event->to);
        $event->update([
            'from' => $newStart,
            'to'   => $newEnd ?? $newStart->copy()->addMinutes($duration),
        ]);

        Notification::make()->success()
            ->title(__('panels/admin/widgets/dashboard.calendar_rescheduled'))
            ->send();

        return true;
    }

    // ─── Resize ───────────────────────────────────────────────────────────────

    public function onEventResize(EventResizeInfo $info, Model $event): bool
    {
        if (! $event instanceof Appointment) {
            return false;
        }

        $newEnd = $info->event->getEnd();
        if ($newEnd) {
            $event->update(['to' => $newEnd]);
            return true;
        }

        return false;
    }

    // ─── Custom Event Content ─────────────────────────────────────────────────

    protected function eventContent(): string
    {
        return '<div class="px-1 py-0.5 text-[11px] leading-tight truncate">'
            . '<div class="font-semibold truncate" x-text="event.title"></div>'
            . '<div class="opacity-75 truncate" x-show="event.extendedProps?.patient" x-text="event.extendedProps?.patient"></div>'
            . '</div>';
    }

    // ─── Filter helpers ───────────────────────────────────────────────────────

    public function updatedFilterStatuses(): void
    {
        $this->refreshRecords();
    }

    public function updatedShowAvailability(): void
    {
        $this->refreshRecords();
    }

    public function updatedShowBlocked(): void
    {
        $this->refreshRecords();
    }

    public function toggleStatus(string $status): void
    {
        if (in_array($status, $this->filterStatuses)) {
            $this->filterStatuses = array_values(array_diff($this->filterStatuses, [$status]));
        } else {
            $this->filterStatuses[] = $status;
        }
        $this->refreshRecords();
    }

    public function getStatusOptions(): array
    {
        return collect(AppointmentStatus::cases())->mapWithKeys(
            fn($case) => [$case->value => $case->getLabel()]
        )->toArray();
    }

    public function getStatusColors(): array
    {
        return [
            AppointmentStatus::PENDING->value   => '#F59E0B',
            AppointmentStatus::CONFIRMED->value => '#3B82F6',
            AppointmentStatus::REJECTED->value  => '#EF4444',
            AppointmentStatus::CANCELLED->value => '#6B7280',
            AppointmentStatus::COMPLETED->value => '#10B981',
        ];
    }

    public function getAvailableViews(): array
    {
        return [
            CalendarViewType::DayGridMonth->value => ['label' => __('panels/admin/widgets/dashboard.calendar_view_month'),   'icon' => 'heroicon-o-calendar'],
            CalendarViewType::TimeGridWeek->value  => ['label' => __('panels/admin/widgets/dashboard.calendar_view_week'),    'icon' => 'heroicon-o-calendar-days'],
            CalendarViewType::TimeGridDay->value   => ['label' => __('panels/admin/widgets/dashboard.calendar_view_day'),     'icon' => 'heroicon-o-sun'],
            CalendarViewType::ListWeek->value      => ['label' => __('panels/admin/widgets/dashboard.calendar_view_list'),    'icon' => 'heroicon-o-list-bullet'],
        ];
    }
}
