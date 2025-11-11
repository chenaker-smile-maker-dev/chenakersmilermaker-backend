<x-filament-panels::page>
    @if ($this->record)
        <div class="space-y-6">
            <!-- Doctor Information Section -->
            {{ $this->infolist }}

            <!-- Schedules Table Section -->
            <h1 class="fi-section-header-heading font-bold mb-4 mt-12">
                Manage Schedules
            </h1>
            {{ $this->table }}
        </div>
    @else
        <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-red-800 dark:border-red-700 dark:bg-red-900/30 dark:text-red-200">
            <p class="font-semibold">Error loading doctor information</p>
            <p class="text-sm">Please try again or contact support if the problem persists.</p>
        </div>
    @endif
</x-filament-panels::page>
