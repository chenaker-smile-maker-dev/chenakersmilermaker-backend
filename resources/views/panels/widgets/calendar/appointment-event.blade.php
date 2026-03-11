{{-- Custom event content for the booking calendar --}}
{{-- Each event is wrapped in an Alpine component. Access event data via `event.extendedProps` --}}
<div class="px-1 py-0.5 text-xs leading-tight truncate">
    <div class="font-semibold truncate" x-text="event.title"></div>
    <div class="opacity-80 truncate" x-show="event.extendedProps.patient" x-text="event.extendedProps.patient"></div>
</div>
