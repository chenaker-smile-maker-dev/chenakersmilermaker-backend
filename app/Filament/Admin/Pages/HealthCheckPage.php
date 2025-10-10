<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\AdminNavigation;
use BackedEnum;
use Filament\Pages\Page;

class HealthCheckPage extends Page
{
    protected static string | BackedEnum | null $navigationIcon = AdminNavigation::HEALTH_PAGE['icon'];
    protected static ?int $navigationSort = AdminNavigation::HEALTH_PAGE['sort'];
    protected string $view = 'filament.admin.pages.health-check-page';
}
