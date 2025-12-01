@php
    use Filament\Support\Facades\FilamentAsset;
    use Guava\Calendar\Enums\Context;
    use Filament\Support\Facades\FilamentColor;
    use Filament\Support\View\Components\ButtonComponent;
@endphp

<div>
    <style>
        /* Customize calendar button styles */
        .fc-button-primary {
            background-color: #3498DB !important;
            border-color: #3498DB !important;
            color: white !important;
        }

        .fc-button-primary:not(:disabled).fc-button-active {
            background-color: #2980B9 !important;
            border-color: #2980B9 !important;
        }

        .fc-button-primary:not(:disabled):hover {
            background-color: #2980B9 !important;
            border-color: #2980B9 !important;
        }

        /* Adjust calendar height for better visibility */
        .fc {
            font-family: inherit;
        }

        .fc .fc-col-header-cell {
            padding: 8px 0;
        }

        .fc .fc-daygrid-day {
            min-height: 100px;
        }

        /* Light mode event styling */
        .fc .fc-event {
            color: white !important;
            font-weight: 500;
        }

        .fc .fc-event-title {
            color: white !important;
            font-weight: 500;
        }

        /* Dark mode support for calendar */
        @media (prefers-color-scheme: dark) {
            .fc {
                background-color: rgb(31, 41, 55);
                color: rgb(229, 231, 235);
            }

            .fc .fc-button-primary:not(:disabled) {
                color: white;
            }

            .fc .fc-col-header-cell,
            .fc .fc-daygrid-day,
            .fc .fc-daygrid-day.fc-day-other {
                background-color: rgb(31, 41, 55);
                border-color: rgb(55, 65, 81);
            }

            .fc .fc-daygrid-day-number,
            .fc .fc-col-header-cell {
                color: rgb(229, 231, 235);
            }

            .fc .fc-daygrid-day:hover {
                background-color: rgb(41, 51, 65);
            }

            .fc .fc-event {
                color: white !important;
            }

            .fc .fc-event-title {
                color: white !important;
            }
        }
    </style>

    <div wire:ignore x-load x-load-src="{{ FilamentAsset::getAlpineComponentSrc('calendar', 'guava/calendar') }}"
        x-data="calendar({
            view: @js($this->calendarView),
            locale: @js($this->getLocale()),
            firstDay: @js($this->getFirstDay()),
            dayMaxEvents: @js($this->getDayMaxEvents()),
            eventContent: @js($this->getEventContentJs()),
            eventClickEnabled: @js($this->isEventClickEnabled()),
            eventDragEnabled: @js($this->isEventDragEnabled()),
            eventResizeEnabled: @js($this->isEventResizeEnabled()),
            noEventsClickEnabled: @js($this->isNoEventsClickEnabled()),
            dateClickEnabled: @js($this->isDateClickEnabled()),
            dateSelectEnabled: @js($this->isDateSelectEnabled()),
            datesSetEnabled: @js($this->isDatesSetEnabled()),
            viewDidMountEnabled: @js($this->isViewDidMountEnabled()),
            eventAllUpdatedEnabled: @js($this->isEventAllUpdatedEnabled()),
            hasDateClickContextMenu: @js($this->hasContextMenu(Context::DateClick)),
            hasDateSelectContextMenu: @js($this->hasContextMenu(Context::DateSelect)),
            hasEventClickContextMenu: @js($this->hasContextMenu(Context::EventClick)),
            hasNoEventsClickContextMenu: @js($this->hasContextMenu(Context::NoEventsClick)),
            resources: @js($this->getResourcesJs()),
            resourceLabelContent: @js($this->getResourceLabelContentJs()),
            theme: @js($this->getTheme()),
            options: @js($this->getOptions()),
            eventAssetUrl: @js(FilamentAsset::getAlpineComponentSrc('calendar-event', 'guava/calendar')),
        })" @class(FilamentColor::getComponentClasses(ButtonComponent::class, 'primary')) x-ref="calendarContainer">
        <div data-calendar></div>
        @if ($this->hasContextMenu())
            <x-guava-calendar::context-menu />
        @endif
    </div>
</div>
