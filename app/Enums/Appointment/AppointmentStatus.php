<?php

namespace App\Enums\Appointment;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum AppointmentStatus: string implements HasLabel, HasColor, HasIcon
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';
    case COMPLETED = 'completed';

    public function getLabel(): string
    {
        return __('panels/admin/resources/appointment.tabs.' . $this->value);
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::CONFIRMED => 'info',
            self::REJECTED => 'danger',
            self::CANCELLED => 'gray',
            self::COMPLETED => 'success',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::PENDING => 'heroicon-o-clock',
            self::CONFIRMED => 'heroicon-o-check-circle',
            self::REJECTED => 'heroicon-o-x-circle',
            self::CANCELLED => 'heroicon-o-trash',
            self::COMPLETED => 'heroicon-o-check-badge',
        };
    }

    /**
     * Returns the list of statuses this status can transition to.
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::PENDING   => [self::CONFIRMED, self::REJECTED, self::CANCELLED],
            self::CONFIRMED => [self::COMPLETED, self::CANCELLED],
            self::REJECTED  => [],
            self::CANCELLED => [],
            self::COMPLETED => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions());
    }
}
