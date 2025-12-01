@php
    use App\Livewire\Widgets\AppointmentMonthViewWidget;
    use App\Livewire\Widgets\AppointmentWeekViewWidget;
    use App\Livewire\Widgets\AppointmentDayViewWidget;
    use App\Livewire\Widgets\AppointmentListMonthViewWidget;
    use App\Livewire\Widgets\AppointmentListWeekViewWidget;
    use App\Livewire\Widgets\AppointmentListDayViewWidget;
@endphp

<x-filament-widgets::widget>
    <x-filament::section class="w-full">
        <div class="p-6">
            {{-- Calendar View Switcher --}}
            <div class="mb-4 flex items-center justify-between gap-2">
                <div class="flex gap-2 flex-wrap">
                    @foreach ([
        'dayGridMonth' => 'Month',
        'timeGridWeek' => 'Week',
        'timeGridDay' => 'Day',
        'listMonth' => 'List Month',
        'listWeek' => 'List Week',
        'listDay' => 'List Day',
    ] as $mode => $label)
                        <x-filament::button wire:click="changeView('{{ $mode }}')"
                            color="{{ $this->currentView === $mode ? 'primary' : 'gray' }}" size="sm">
                            {{ $label }}
                        </x-filament::button>
                    @endforeach
                </div>
            </div>

            {{-- Dynamic Widget Rendering --}}
            <div>
                @switch($this->currentView)
                    @case('dayGridMonth')
                        @livewire('App\Livewire\Widgets\AppointmentMonthViewWidget', key('month-' . time()))
                    @break

                    @case('timeGridWeek')
                        @livewire('App\Livewire\Widgets\AppointmentWeekViewWidget', key('week-' . time()))
                    @break

                    @case('timeGridDay')
                        @livewire('App\Livewire\Widgets\AppointmentDayViewWidget', key('day-' . time()))
                    @break

                    @case('listMonth')
                        @livewire('App\Livewire\Widgets\AppointmentListMonthViewWidget', key('listmonth-' . time()))
                    @break

                    @case('listWeek')
                        @livewire('App\Livewire\Widgets\AppointmentListWeekViewWidget', key('listweek-' . time()))
                    @break

                    @case('listDay')
                        @livewire('App\Livewire\Widgets\AppointmentListDayViewWidget', key('listday-' . time()))
                    @break

                    @default
                        @livewire('App\Livewire\Widgets\AppointmentMonthViewWidget', key('default-' . time()))
                @endswitch
            </div>

            {{-- Doctor Color Legend --}}
            <div class="mt-6">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Doctor Color Reference</h4>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
                    @php
                        $doctors = \App\Models\Doctor::all();
                        $colors = $this->getDoctorColors();
                    @endphp

                    @foreach ($doctors as $doctor)
                        @php
                            $colorIndex = ($doctor->id - 1) % count($colors);
                            $color = $colors[$colorIndex];
                        @endphp
                        <div class="flex items-center gap-2">
                            <div class="w-4 h-4 rounded flex-shrink-0 shadow-sm"
                                style="background-color: {{ $color }}">
                            </div>
                            <span class="text-xs text-gray-700 dark:text-gray-300 truncate">{{ $doctor->name }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
