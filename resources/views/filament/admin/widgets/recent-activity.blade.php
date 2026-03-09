<x-filament-widgets::widget>
    <x-filament::section heading="Recent Activity">
        <div class="space-y-3">
            @forelse($this->getActivities() as $activity)
                <div class="flex items-start gap-3 p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-white/5 transition">
                    <div class="flex-shrink-0 mt-0.5">
                        <x-filament::icon
                            :icon="$activity['icon']"
                            class="w-5 h-5 text-{{ $activity['color'] }}-500"
                        />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                            {{ $activity['title'] }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                            {{ $activity['description'] }}
                        </p>
                    </div>
                    <div class="flex-shrink-0 text-xs text-gray-400 dark:text-gray-500 whitespace-nowrap">
                        {{ $activity['time']->diffForHumans() }}
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">
                    No recent activity
                </p>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
