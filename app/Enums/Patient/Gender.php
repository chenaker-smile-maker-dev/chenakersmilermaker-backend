<?php

namespace App\Enums\Patient;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum Gender: string implements HasLabel, HasColor, HasIcon
{
    case MALE = 'male';
    case FEMALE = 'female';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function getLabel(): string
    {
        return __('panels/admin/resources/patient.tabs.' . $this->value);
    }

    public function getColor(): string
    {
        return match ($this) {
            self::MALE   => 'info',
            self::FEMALE => 'pink',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::MALE   => 'heroicon-o-user',
            self::FEMALE => 'heroicon-o-user-circle',
        };
    }
}
