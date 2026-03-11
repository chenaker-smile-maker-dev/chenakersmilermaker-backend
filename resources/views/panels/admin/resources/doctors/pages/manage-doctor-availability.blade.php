<x-filament-panels::page>
    @if ($this->record)
        {{ $this->table }}
    @else
        <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-red-800 dark:border-red-700 dark:bg-red-900/30 dark:text-red-200">
            <p class="font-semibold">{{ __('panels/admin/resources/doctor.schedule.error_loading') }}</p>
        </div>
    @endif
</x-filament-panels::page>
