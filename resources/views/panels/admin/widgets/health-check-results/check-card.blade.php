@props(['result'])

@php
    use Spatie\Health\Enums\Status;

    $bgColorMap = [
        Status::ok()->value => 'bg-success-50 dark:bg-success-950/50 border-success-300 dark:border-success-800',
        Status::warning()->value => 'bg-warning-50 dark:bg-warning-950/50 border-warning-300 dark:border-warning-800',
        Status::failed()->value => 'bg-danger-50 dark:bg-danger-950/50 border-danger-300 dark:border-danger-800',
    ];

    $bgColor = $bgColorMap[$result['status']] ?? 'bg-gray-50 dark:bg-gray-900 border-gray-300 dark:border-gray-700';
@endphp

<div class="relative group border-2 rounded-xl p-6 transition-all duration-200 hover:shadow-xl hover:scale-105 {{ $bgColor }}">
    <!-- Status Icon at the top -->
    <div class="flex justify-center mb-4">
        @include('panels.admin.widgets.health-check-results.status-icon', ['status' => $result['status']])
    </div>

    <!-- Card Content -->
    <div class="space-y-3">
        <!-- Title -->
        <h3 class="text-base font-bold text-gray-900 dark:text-white text-center leading-tight">
            {{ $result['label'] }}
        </h3>

        <!-- Status Badge -->
        {{-- <div class="flex justify-center">
            @include('panels.admin.widgets.health-check-results.status-badge', ['status' => $result['status']])
        </div> --}}

        <!-- Summary Section -->
        @if($result['shortSummary'])
            <div class="pt-2 border-t border-gray-200 dark:border-gray-700">
                <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 text-center">
                    {{ $result['shortSummary'] }}
                </p>
            </div>
        @endif

        <!-- Message Section -->
        @if($result['notificationMessage'])
            <div class="pt-2">
                <p class="text-xs text-gray-600 dark:text-gray-400 text-center line-clamp-3 leading-relaxed">
                    {{ $result['notificationMessage'] }}
                </p>
            </div>
        @endif
    </div>

    <!-- Metadata Tooltip -->
    @include('panels.admin.widgets.health-check-results.metadata-tooltip', ['meta' => $result['meta']])

    <!-- Hover indicator for metadata -->
    {{-- @if(!empty($result['meta']))
        <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
            <x-filament::icon
                icon="heroicon-o-information-circle"
                class="h-5 w-5 text-gray-500 dark:text-gray-400"
            />
        </div>
    @endif --}}
</div>
