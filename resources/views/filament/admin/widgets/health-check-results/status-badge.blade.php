@props(['status'])

@php
    use Spatie\Health\Enums\Status;

    $colorMap = [
        Status::ok()->value => 'success',
        Status::warning()->value => 'warning',
        Status::failed()->value => 'danger',
    ];

    $color = $colorMap[$status] ?? 'gray';
@endphp

<x-filament::badge :color="$color">
    {{ ucfirst($status) }}
</x-filament::badge>
