<x-filament-widgets::widget>
    <x-filament::section>
        @php
            $data = $this->getCheckResults();
            $results = $data['results'];
            $finishedAt = $data['finishedAt'];
        @endphp
hello world
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <span>Health Check Results</span>
                @if($finishedAt)
                    <span class="text-xs font-normal text-gray-500 dark:text-gray-400">
                        (Last run: {{ $finishedAt->diffForHumans() }})
                    </span>
                @endif
            </div>
        </x-slot>

        @if($results->isEmpty())
            @include('panels.admin.widgets.health-check-results.empty-state')
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($results as $result)
                    @include('panels.admin.widgets.health-check-results.check-card', ['result' => $result])
                @endforeach
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
