<?php

namespace App\Providers\Filament;

use DutchCodingCompany\FilamentDeveloperLogins\FilamentDeveloperLoginsPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Joaopaulolndev\FilamentEditProfile\FilamentEditProfilePlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()

            ->colors([
                'primary' => Color::hex('#8FFDC6'),
                'success' => Color::hex('#12D18E'),
                'error' => Color::hex('#F85556'),
                'warning' => Color::hex('#FF9500'),
                'info' => Color::hex('#F037A5'),
                'neutral' => Color::hex('#E5E7EB'),
            ])
            ->favicon(fn() => asset('favicon.svg'))
            ->brandLogo(fn() => view('panels.admin.components.brand'))
            ->darkModeBrandLogo(fn() => view('panels.admin.components.brand-dark'))
            ->brandLogoHeight('2rem')
            ->font('Inter')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->databaseTransactions()
            ->databaseNotifications()
            ->databaseNotificationsPolling("30s")
            ->lazyLoadedDatabaseNotifications(false)

            ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\Filament\Admin\Resources')
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\Filament\Admin\Pages')
            ->discoverWidgets(in: app_path('Filament/Admin/Widgets'), for: 'App\Filament\Admin\Widgets')

            ->pages([Dashboard::class])
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class
            ])

            ->plugins([
                FilamentDeveloperLoginsPlugin::make()->enabled(config('app.debug'))->users(['ADMINISTRATEUR' => 'admin@admin.dev']),
                FilamentEditProfilePlugin::make()->setIcon('heroicon-o-user-circle')
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([Authenticate::class]);
    }
}
