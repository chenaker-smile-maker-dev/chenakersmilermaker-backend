<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\AdminNavigation;
use App\Filament\Admin\Widgets\HealthCheckResultsWidget;
use App\Filament\Admin\Widgets\HealthCheckStatsWidget;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;
use Spatie\Health\Commands\RunHealthChecksCommand;

class HealthCheckPage extends Page
{
    protected static string | BackedEnum | null $navigationIcon = AdminNavigation::HEALTH_PAGE['icon'];
    protected static ?int $navigationSort = AdminNavigation::HEALTH_PAGE['sort'];
    protected string $view = 'panels.admin.pages.health-check-page';

    public static function getNavigationGroup(): ?string
    {
        return  __(AdminNavigation::HEALTH_PAGE['group']);
    }

    public function mount(): void
    {
        if (request()->has('fresh')) {
            $this->runFreshCheck();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->visible(false)
                ->label('Run Fresh Check')
                ->icon('heroicon-o-arrow-path')
                ->color('primary')
                ->action(function () {
                    $this->runFreshCheck();
                    $this->dispatch('$refresh');
                })
                ->requiresConfirmation(false),
        ];
    }

    protected function runFreshCheck(): void
    {
        Artisan::call(RunHealthChecksCommand::class);

        Notification::make()
            ->success()
            ->title('Health checks executed successfully.')
            ->body('The latest health check results are now available.')
            ->send();

        $this->redirect(self::getUrl());
    }

    protected function getHeaderWidgets(): array
    {
        return [
            HealthCheckStatsWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            HealthCheckResultsWidget::class,
        ];
    }

    public function getColumns(): int | string | array
    {
        return [
            'default' => 1,
            'sm' => 1,
            'md' => 2,
            'lg' => 4,
            'xl' => 4,
            '2xl' => 4,
        ];
    }
}
