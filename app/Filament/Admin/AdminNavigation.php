<?php

namespace App\Filament\Admin;

use Filament\Support\Icons\Heroicon;

class AdminNavigation
{

    public const MANAGEMENT_GROUP = 'Management';

    public const DOCTORS_RESOURCE = [
        'icon' => Heroicon::OutlinedUsers,
        'sort' => 1,
        'group' => self::MANAGEMENT_GROUP,
    ];
}
