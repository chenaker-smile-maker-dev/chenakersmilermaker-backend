<?php

namespace App\Filament\Admin;

use Filament\Support\Icons\Heroicon;

class AdminNavigation
{

    public const MANAGEMENT_GROUP = 'Management';
    public const CONFIGURATION_GROUP = 'Configuration Group';
    public const WEBSITE_CONTENT_GROUP = 'Website Content';

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

    public const SETTINGS_PAGE = [
        'icon' => Heroicon::OutlinedCog6Tooth,
        'sort' => 99,
        'group' => self::CONFIGURATION_GROUP,
    ];

    public const HEALTH_PAGE = [
        'icon' => Heroicon::ShieldCheck,
        'sort' => 100,
        'group' => self::CONFIGURATION_GROUP,
        // 'group' => self::MANAGEMENT_GROUP,
    ];

    public const EVENTS_RESOURCE = [
        'icon' => Heroicon::CalendarDays,
        'sort' => 5,
        'group' => self::WEBSITE_CONTENT_GROUP,
    ];

    public const TRAININGS_RESOURCE = [
        'icon' => Heroicon::OutlinedAcademicCap,
        'sort' => 6,
        'group' => self::WEBSITE_CONTENT_GROUP,
    ];

    public const TESTIMONIALS_RESOURCE = [
        'icon' => Heroicon::StarSmall,
        'sort' => 7,
        'group' => self::WEBSITE_CONTENT_GROUP,
    ];
}
