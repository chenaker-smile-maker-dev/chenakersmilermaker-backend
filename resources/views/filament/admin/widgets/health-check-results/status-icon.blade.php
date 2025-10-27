@props(['status'])

@php
    use Spatie\Health\Enums\Status;

    $iconMap = [
        Status::ok()->value => 'heroicon-o-check-circle',
        Status::warning()->value => 'heroicon-o-exclamation-triangle',
        Status::failed()->value => 'heroicon-o-x-circle',
    ];

    $colorMap = [
        Status::ok()->value => 'text-success-600 dark:text-success-400',
        Status::warning()->value => 'text-warning-600 dark:text-warning-400',
        Status::failed()->value => 'text-danger-600 dark:text-danger-400',
    ];

    $icon = $iconMap[$status] ?? 'heroicon-o-question-mark-circle';
    $color = $colorMap[$status] ?? 'text-gray-600 dark:text-gray-400';
@endphp

<x-filament::icon
    :icon="$icon"
    class="h-12 w-12 {{ $color }}"
/>
