<?php

namespace App\Providers\Filament;

use DutchCodingCompany\FilamentDeveloperLogins\FilamentDeveloperLoginsPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use App\Filament\Admin\Pages\Dashboard;
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
use CharrafiMed\GlobalSearchModal\GlobalSearchModalPlugin;
use Filament\Enums\GlobalSearchPosition;
use Hammadzafar05\MobileBottomNav\MobileBottomNav;
use Caresome\FilamentAuthDesigner\AuthDesignerPlugin;
use Caresome\FilamentAuthDesigner\Data\AuthPageConfig;
use Caresome\FilamentAuthDesigner\Enums\MediaPosition;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->spa()
            ->sidebarCollapsibleOnDesktop()
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
            ->font('Poppins')
            ->viteTheme('resources/css/filament/admin/theme.css')

            ->databaseTransactions()
            ->databaseNotifications()
            ->databaseNotificationsPolling("30s")
            ->lazyLoadedDatabaseNotifications(false)
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            // ->globalSearch(position: GlobalSearchPosition::Topbar)

            ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\Filament\Admin\Resources')
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\Filament\Admin\Pages')
            // ->discoverWidgets(in: app_path('Filament/Admin/Widgets'), for: 'App\Filament\Admin\Widgets')

            ->pages([])
            ->plugins([
                FilamentDeveloperLoginsPlugin::make()->enabled(config('app.debug'))->users(['ADMINISTRATEUR' => 'admin@admin.dev']),
                FilamentEditProfilePlugin::make()->setIcon('heroicon-o-user-circle'),
                GlobalSearchModalPlugin::make()->highlighter(true)->modal(slideOver: true),
                MobileBottomNav::make(),
                AuthDesignerPlugin::make()
                    ->defaults(
                        fn(AuthPageConfig $config) => $config
                            ->media('https://picsum.photos/1920/1080', alt: 'Authentication background')
                            ->mediaPosition(MediaPosition::Right)
                            ->blur(8)
                    )
                    ->login()
                    ->themeToggle(top: '1.5rem', right: '1.5rem'),
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
