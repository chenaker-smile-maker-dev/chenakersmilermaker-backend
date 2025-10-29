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

    public const PATIENTS_RESOURCE = [
        'icon' => Heroicon::UserGroup,
        'sort' => 2,
        'group' => self::MANAGEMENT_GROUP,
    ];

    public const SERVICES_RESOURCE = [
        'icon' => Heroicon::Briefcase,
        'sort' => 3,
        'group' => self::MANAGEMENT_GROUP,
    ];

    public const APPOINTEMENT_RESOURCE = [
        'icon' => Heroicon::CalendarDays,
        'sort' => 4,
        'group' => self::MANAGEMENT_GROUP,
    ];

    public const HEALTH_PAGE = [
        'icon' => Heroicon::ShieldCheck,
        'sort' => 9999,
        // 'group' => self::MANAGEMENT_GROUP,
    ];
}
