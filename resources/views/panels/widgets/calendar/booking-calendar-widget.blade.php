@php
    use Filament\Support\Facades\FilamentAsset;
    use Guava\Calendar\Enums\Context;
    use Filament\Support\Facades\FilamentColor;
    use Filament\Support\View\Components\ButtonComponent;
@endphp

<x-filament-widgets::widget>
    <x-filament::section
        :after-header="$this->getCachedHeaderActionsComponent()"
        :footer="$this->getCachedFooterActionsComponent()"
    >
        <x-slot name="heading">
            {{ $this->getHeading() }}
        </x-slot>

        <style>
            .ec-event.ec-preview, .ec-now-indicator { z-index: 30; }
            /* Today cell: info/blue instead of the default yellow-green */
            .fc-day-today { background-color: rgb(59 130 246 / 0.10) !important; }
            .fc-timegrid-col.fc-day-today { background-color: rgb(59 130 246 / 0.10) !important; }
            .fc-list-day.fc-day-today .fc-list-day-cushion { background-color: rgb(59 130 246 / 0.15) !important; }
            /* RTL: flip prev/next chevron icons so they point in the direction of travel */
            .fc-direction-rtl .fc-prev-button .fc-icon,
            .fc-direction-rtl .fc-next-button .fc-icon { transform: scaleX(-1); }
        </style>

        {{-- ── View Type Switcher ──────────────────────────────────────────────── --}}
        <div class="mb-4 flex flex-wrap items-center gap-1.5">
            @foreach($this->getAvailableViews() as $viewKey => $viewInfo)
                <button
                    wire:click="switchView('{{ $viewKey }}')"
                    type="button"
                    @class([
                        'inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-medium transition-all duration-150',
                        'bg-primary-600 text-white shadow-sm' => $activeView === $viewKey,
                        'bg-gray-100 dark:bg-white/10 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-white/20' => $activeView !== $viewKey,
                    ])
                >
                    <x-dynamic-component :component="$viewInfo['icon']" class="w-3.5 h-3.5" />
                    {{ $viewInfo['label'] }}
                </button>
            @endforeach
        </div>

        {{-- ── Filter Bar ──────────────────────────────────────────────────────── --}}
        <div class="mb-5 space-y-4">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start">
                {{-- Doctor multi-select dropdown --}}
                <div x-data="{ open: false }" class="relative flex-1 min-w-[220px]" @click.outside="open = false">
                    <button @click="open = !open" type="button"
                        class="w-full flex items-center justify-between gap-2 rounded-lg border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 px-3 py-2.5 text-sm text-gray-700 dark:text-gray-200 shadow-sm hover:bg-gray-50 dark:hover:bg-white/10 transition"
                    >
                        <span class="flex items-center gap-2 truncate">
                            <x-heroicon-m-funnel class="w-4 h-4 text-gray-400 shrink-0" />
                            @if(empty($filterDoctorIds))
                                <span class="text-gray-500 dark:text-gray-400">{{ __('panels/admin/widgets/dashboard.calendar_all_doctors') }}</span>
                            @else
                                <span>{{ __('panels/admin/widgets/dashboard.calendar_doctors_selected', ['count' => count($filterDoctorIds)]) }}</span>
                            @endif
                        </span>
                        <x-heroicon-m-chevron-down class="w-4 h-4 text-gray-400 shrink-0 transition-transform" ::class="{ 'rotate-180': open }" />
                    </button>
                    <div x-show="open" x-transition.origin.top.left
                        class="absolute z-50 mt-1 w-full min-w-[260px] rounded-lg bg-white dark:bg-gray-900 shadow-lg ring-1 ring-gray-950/5 dark:ring-white/10 max-h-60 overflow-y-auto"
                    >
                        @if($filterDoctorIds)
                            <button wire:click="$set('filterDoctorIds', [])" type="button"
                                class="w-full text-left px-3 py-2 text-xs font-medium text-primary-600 dark:text-primary-400 hover:bg-gray-50 dark:hover:bg-white/5 border-b border-gray-100 dark:border-white/5">
                                {{ __('panels/admin/widgets/dashboard.calendar_all_doctors') }}
                            </button>
                        @endif
                        @foreach($this->getDoctors() as $doctor)
                            <label class="flex items-center gap-3 px-3 py-2.5 hover:bg-gray-50 dark:hover:bg-white/5 cursor-pointer transition">
                                <input type="checkbox" value="{{ $doctor->id }}" wire:model.live="filterDoctorIds"
                                    class="rounded border-gray-300 dark:border-gray-600 text-primary-600 shadow-sm focus:ring-primary-500/25 dark:bg-white/10" />
                                <span class="text-sm text-gray-700 dark:text-gray-200">{{ $doctor->display_name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Status pill toggles --}}
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 me-1">
                    {{ __('panels/admin/widgets/dashboard.calendar_filter_status') }}
                </span>
                @foreach($this->getStatusOptions() as $value => $label)
                    @php $active = in_array($value, $filterStatuses); @endphp
                    <button wire:click="toggleStatus('{{ $value }}')" type="button"
                        @class([
                            'inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-medium border transition-all duration-150',
                            'ring-2 ring-offset-1 dark:ring-offset-gray-900 shadow-sm' => $active,
                            'opacity-40 hover:opacity-70' => !$active,
                        ])
                        style="
                            background-color: {{ $this->getStatusColors()[$value] }}{{ $active ? '25' : '10' }};
                            color: {{ $this->getStatusColors()[$value] }};
                            border-color: {{ $this->getStatusColors()[$value] }}{{ $active ? '60' : '25' }};
                            {{ $active ? '--tw-ring-color: ' . $this->getStatusColors()[$value] . '40;' : '' }}
                        "
                    >
                        <span class="w-2 h-2 rounded-full shrink-0" style="background-color: {{ $this->getStatusColors()[$value] }}"></span>
                        {{ $label }}
                    </button>
                @endforeach
                @if(!empty($filterStatuses))
                    <button wire:click="$set('filterStatuses', [])" type="button"
                        class="text-xs text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 underline decoration-dotted underline-offset-2 transition">
                        {{ __('panels/admin/widgets/dashboard.calendar_clear_filter') }}
                    </button>
                @endif
            </div>
        </div>

        {{-- ── Calendar ────────────────────────────────────────────────────────── --}}
        <div
            wire:ignore
            x-load
            x-load-src="{{ FilamentAsset::getAlpineComponentSrc('calendar', 'guava/calendar') }}"
            x-data="calendar({
                view: @js($this->getCalendarView()),
                locale: @js($this->getLocale()),
                firstDay: @js($this->getFirstDay()),
                dayMaxEvents: @js($this->getDayMaxEvents()),
                eventContent: @js($this->getEventContentJs()),
                eventClickEnabled: @js($this->isEventClickEnabled()),
                eventDragEnabled: @js($this->isEventDragEnabled()),
                eventResizeEnabled: @js($this->isEventResizeEnabled()),
                noEventsClickEnabled: @js($this->isNoEventsClickEnabled()),
                dateClickEnabled: @js($this->isDateClickEnabled()),
                dateSelectEnabled: @js($this->isDateSelectEnabled()),
                datesSetEnabled: @js($this->isDatesSetEnabled()),
                viewDidMountEnabled: @js($this->isViewDidMountEnabled()),
                eventAllUpdatedEnabled: @js($this->isEventAllUpdatedEnabled()),
                hasDateClickContextMenu: @js($this->hasContextMenu(Context::DateClick)),
                hasDateSelectContextMenu: @js($this->hasContextMenu(Context::DateSelect)),
                hasEventClickContextMenu: @js($this->hasContextMenu(Context::EventClick)),
                hasNoEventsClickContextMenu: @js($this->hasContextMenu(Context::NoEventsClick)),
                resources: @js($this->getResourcesJs()),
                resourceLabelContent: @js($this->getResourceLabelContentJs()),
                theme: @js($this->getTheme()),
                options: @js($this->getOptions()),
                eventAssetUrl: @js(FilamentAsset::getAlpineComponentSrc('calendar-event', 'guava/calendar')),
            })"
            @class(FilamentColor::getComponentClasses(ButtonComponent::class, 'primary'))
        >
            <div data-calendar></div>
            @if($this->hasContextMenu())
                <x-guava-calendar::context-menu/>
            @endif
        </div>

        {{-- ── Legend ───────────────────────────────────────────────────────────── --}}
        <div class="mt-5 pt-4 border-t border-gray-200 dark:border-white/10">
            <div class="flex flex-wrap items-center gap-4">
                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                    {{ __('panels/admin/widgets/dashboard.calendar_status_legend') }}
                </span>
                @foreach($this->getStatusColors() as $status => $color)
                    <div class="flex items-center gap-1.5">
                        <span class="inline-block w-3 h-3 rounded-full shrink-0" style="background-color: {{ $color }}"></span>
                        <span class="text-xs text-gray-600 dark:text-gray-300">
                            {{ __('panels/admin/widgets/dashboard.calendar_status_' . $status) }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

    </x-filament::section>
    <x-filament-actions::modals/>
</x-filament-widgets::widget>
