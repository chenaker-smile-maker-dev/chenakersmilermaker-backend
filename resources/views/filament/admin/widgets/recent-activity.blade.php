<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Recent Activity</x-slot>

        @php $activities = $this->getActivities(); @endphp

        @if($activities->isEmpty())
            <p class="text-sm text-gray-500 dark:text-gray-400">No recent activity.</p>
        @else
            <div class="space-y-3">
                @foreach($activities as $activity)
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 mt-0.5">
                            <x-filament::icon
                                :icon="$activity['icon']"
                                class="h-5 w-5 text-{{ $activity['color'] }}-500"
                            />
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                {{ $activity['title'] }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                {{ $activity['description'] }}
                            </p>
                        </div>
                        <div class="flex-shrink-0 text-xs text-gray-400 dark:text-gray-500 whitespace-nowrap">
                            {{ $activity['created_at']?->diffForHumans() }}
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
