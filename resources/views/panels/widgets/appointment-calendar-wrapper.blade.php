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

    {{-- Appointment Modal --}}
    <x-filament::modal id="appointmentModal" :open="$this->selectedAppointmentId !== null" @close="$wire.closeAppointmentModal()">
        @php
            $appointment = $this->getSelectedAppointment();
        @endphp

        <x-slot name="heading">
            {{ $this->modalMode === 'edit' ? 'Edit Appointment' : 'View Appointment' }}
        </x-slot>

        @if ($appointment)
            @if ($this->modalMode === 'view')
                {{-- View Mode - Display Infolist --}}
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Service</p>
                            <p class="text-base font-semibold text-gray-900 dark:text-white">
                                {{ $appointment->service->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Doctor</p>
                            <p class="text-base font-semibold text-gray-900 dark:text-white">
                                {{ $appointment->doctor->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Patient</p>
                            <p class="text-base font-semibold text-gray-900 dark:text-white">
                                {{ $appointment->patient->full_name }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</p>
                            <p class="text-base font-semibold text-gray-900 dark:text-white">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                    style="background-color: #3B82F6; color: white;">
                                    {{ $appointment->status->name }}
                                </span>
                            </p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">From</p>
                            <p class="text-base font-semibold text-gray-900 dark:text-white">
                                {{ $appointment->from->format('M d, Y H:i') }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">To</p>
                            <p class="text-base font-semibold text-gray-900 dark:text-white">
                                {{ $appointment->to->format('M d, Y H:i') }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Price</p>
                            <p class="text-base font-semibold text-gray-900 dark:text-white">
                                {{ number_format($appointment->price, 0) }} DZD</p>
                        </div>
                    </div>
                </div>
            @else
                {{-- Edit Mode - Display Simple Form --}}
                <form wire:submit="saveAppointment" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">From</label>
                        <input type="datetime-local" wire:model="editFromDate"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                            required />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">To</label>
                        <input type="datetime-local" wire:model="editToDate"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                            required />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Doctor</label>
                        <select wire:model="editDoctorId"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                            required>
                            <option value="">Select Doctor</option>
                            @foreach (\App\Models\Doctor::all() as $doctor)
                                <option value="{{ $doctor->id }}">{{ $doctor->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Service</label>
                        <select wire:model="editServiceId"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                            required>
                            <option value="">Select Service</option>
                            @foreach (\App\Models\Service::all() as $service)
                                <option value="{{ $service->id }}">{{ $service->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Patient</label>
                        <select wire:model="editPatientId"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                            required>
                            <option value="">Select Patient</option>
                            @foreach (\App\Models\Patient::all() as $patient)
                                <option value="{{ $patient->id }}">{{ $patient->full_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Price
                            (DZD)</label>
                        <input type="number" wire:model="editPrice"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                            required />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                        <select wire:model="editStatus"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                            required>
                            @foreach (\App\Enums\Appointment\AppointmentStatus::cases() as $status)
                                <option value="{{ $status->value }}">{{ $status->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            @endif
        @endif

        <x-slot name="footerActions">
            @if ($this->modalMode === 'view')
                <x-filament::button wire:click="switchToEditMode({{ $appointment->id ?? 'null' }})" color="primary">
                    Edit
                </x-filament::button>
                <x-filament::button wire:click="goToAppointment({{ $appointment->id ?? 'null' }})" color="info">
                    Go to
                </x-filament::button>
                <x-filament::button x-on:click="close()" color="gray">
                    Close
                </x-filament::button>
            @else
                <x-filament::button type="submit" wire:click="saveAppointment()" color="primary">
                    Save
                </x-filament::button>
                <x-filament::button x-on:click="close()" color="gray">
                    Cancel
                </x-filament::button>
            @endif
        </x-slot>
    </x-filament::modal>
</x-filament-widgets::widget>
