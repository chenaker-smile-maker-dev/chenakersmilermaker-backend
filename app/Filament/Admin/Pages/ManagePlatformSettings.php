<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\AdminNavigation;
use App\Settings\PlatformSettings;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ManagePlatformSettings extends SettingsPage
{
    protected static string|BackedEnum|null $navigationIcon = AdminNavigation::SETTINGS_PAGE['icon'];
    protected static ?int $navigationSort = AdminNavigation::SETTINGS_PAGE['sort'];

    protected static string $settings = PlatformSettings::class;

    public static function getNavigationGroup(): ?string
    {
        return  __(AdminNavigation::SETTINGS_PAGE['group']);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([

                        TimePicker::make('start_time')
                            ->required(),
                        TimePicker::make('end_time')
                            ->required(),
                    ])
            ]);
    }
}
