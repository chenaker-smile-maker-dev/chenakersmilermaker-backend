<?php

namespace App\Enums\Appointment;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ChangeRequestStatus: string implements HasLabel, HasColor, HasIcon
{
    case PENDING_CANCELLATION = 'pending_cancellation';
    case PENDING_RESCHEDULE = 'pending_reschedule';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';

    public function getLabel(): string
    {
        return __('panels/admin/resources/appointment.change_request_status.' . $this->value);
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::PENDING_CANCELLATION => 'danger',
            self::PENDING_RESCHEDULE   => 'warning',
            self::APPROVED             => 'success',
            self::REJECTED             => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::PENDING_CANCELLATION => 'heroicon-o-x-circle',
            self::PENDING_RESCHEDULE   => 'heroicon-o-clock',
            self::APPROVED             => 'heroicon-o-check-circle',
            self::REJECTED             => 'heroicon-o-no-symbol',
        };
    }
}
