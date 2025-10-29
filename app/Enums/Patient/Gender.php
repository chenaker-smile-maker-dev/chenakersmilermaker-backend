<?php

namespace App\Enums\Patient;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

//fiiliament color label and iicon
// enum Gender: string
enum Gender: string implements HasLabel, HasColor
{
    case MALE = 'male';
    case FEMALE = 'female';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
    public function getLabel(): string
    {
        return match ($this) {
            self::MALE => 'Male',
            self::FEMALE => "Female",
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::MALE => 'success',
            self::FEMALE => "danger",
        };
    }

    // public function getIcon(): string
    // {
    //     return match ($this) {
    //         self::MALE => 'Male',
    //         self::FEMALE => "Female",
    //     };
    // }
}
