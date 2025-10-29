<?php

namespace App\Enums\Service;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ServiceAvailability: string implements HasLabel, HasColor, HasIcon
{
    case DAYTIME = 'daytime';
    case NIGHTTIME = 'nighttime';
    case BOTH = 'both';


    public function getLabel(): string
    {
        return match ($this) {
            self::DAYTIME => 'Working Hours',
            self::NIGHTTIME => "Urgence (NIGHT)",
            self::BOTH => "Both",
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::DAYTIME => 'primary',
            self::NIGHTTIME => "danger",
            self::BOTH => "warning",
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::DAYTIME => 'heroicon-m-sun',
            self::NIGHTTIME => 'heroicon-m-moon',
            self::BOTH => 'heroicon-m-clock',
        };
    }
}
