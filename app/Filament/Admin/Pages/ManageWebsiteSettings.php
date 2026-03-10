<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\AdminNavigation;
use App\Settings\WebsiteSettings;
use BackedEnum;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ManageWebsiteSettings extends SettingsPage
{
    protected static string|BackedEnum|null $navigationIcon = AdminNavigation::WEBSITE_SETTINGS_PAGE['icon'];
    protected static ?int $navigationSort = AdminNavigation::WEBSITE_SETTINGS_PAGE['sort'];

    protected static string $settings = WebsiteSettings::class;

    public static function getNavigationGroup(): ?string
    {
        return __(AdminNavigation::WEBSITE_SETTINGS_PAGE['group']);
    }

    public static function getNavigationLabel(): string
    {
        return __('panels/admin/pages/website_settings.title');
    }

    public function getTitle(): string
    {
        return __('panels/admin/pages/website_settings.title');
    }

    public function getHeading(): string
    {
        return __('panels/admin/pages/website_settings.title');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([

            Section::make(__('panels/admin/pages/website_settings.hero_section'))
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    TextInput::make('hero_title')
                        ->label(__('panels/admin/pages/website_settings.hero_title'))
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),
                    Textarea::make('hero_subtitle')
                        ->label(__('panels/admin/pages/website_settings.hero_subtitle'))
                        ->rows(2)
                        ->maxLength(500)
                        ->columnSpanFull(),
                    TextInput::make('hero_cta_text')
                        ->label(__('panels/admin/pages/website_settings.hero_cta_text'))
                        ->maxLength(100),
                ]),

            Section::make(__('panels/admin/pages/website_settings.about_section'))
                ->columns(1)
                ->columnSpanFull()
                ->schema([
                    TextInput::make('about_title')
                        ->label(__('panels/admin/pages/website_settings.about_title'))
                        ->required()
                        ->maxLength(255),
                    Textarea::make('about_description')
                        ->label(__('panels/admin/pages/website_settings.about_description'))
                        ->rows(4)
                        ->maxLength(2000),
                ]),

            Section::make(__('panels/admin/pages/website_settings.contact_section'))
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    TextInput::make('contact_phone')
                        ->label(__('panels/admin/pages/website_settings.contact_phone'))
                        ->tel()
                        ->maxLength(50),
                    TextInput::make('contact_email')
                        ->label(__('panels/admin/pages/website_settings.contact_email'))
                        ->email()
                        ->maxLength(255),
                    TextInput::make('contact_address')
                        ->label(__('panels/admin/pages/website_settings.contact_address'))
                        ->maxLength(500)
                        ->columnSpanFull(),
                    TextInput::make('contact_map_url')
                        ->label(__('panels/admin/pages/website_settings.contact_map_url'))
                        ->url()
                        ->maxLength(1000)
                        ->placeholder('https://maps.google.com/...')
                        ->columnSpanFull(),
                ]),

            Section::make(__('panels/admin/pages/website_settings.social_section'))
                ->columns(3)
                ->columnSpanFull()
                ->schema([
                    TextInput::make('social_facebook')
                        ->label('Facebook')
                        ->url()
                        ->maxLength(255)
                        ->placeholder('https://facebook.com/...'),
                    TextInput::make('social_instagram')
                        ->label('Instagram')
                        ->url()
                        ->maxLength(255)
                        ->placeholder('https://instagram.com/...'),
                    TextInput::make('social_youtube')
                        ->label('YouTube')
                        ->url()
                        ->maxLength(255)
                        ->placeholder('https://youtube.com/...'),
                ]),

        ]);
    }
}
