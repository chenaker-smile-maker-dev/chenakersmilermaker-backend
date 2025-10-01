<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Spatie\Health\Facades\Health;

class HealthCheckServiceProvider extends ServiceProvider
{

    public function register(): void {}

    public function boot(): void
    {
        $this->configureHealthCheck();
    }

    private function configureHealthCheck(): void
    {
        Health::checks([
            ...$this->configureDbChecks(),
            ...$this->configureAppChecks(),
            ...$this->configurePerformanceChecks(),
            ...$this->configureWorkersCheck(),
            ...$this->configureSecurityCheck(),
        ]);
    }
    private function configureDbChecks(): array
    {
        return [
            \Spatie\Health\Checks\Checks\DatabaseCheck::new(),
            \Spatie\Health\Checks\Checks\DatabaseSizeCheck::new()
                ->failWhenSizeAboveGb(errorThresholdGb: 5.0),
            \Spatie\Health\Checks\Checks\DatabaseConnectionCountCheck::new()
                ->warnWhenMoreConnectionsThan(50)
                ->failWhenMoreConnectionsThan(100),
        ];
    }
    private function configureAppChecks(): array
    {
        return [
            \Spatie\Health\Checks\Checks\CacheCheck::new(),
            \Spatie\Health\Checks\Checks\OptimizedAppCheck::new(),
            \Spatie\Health\Checks\Checks\DebugModeCheck::new(),
            \Spatie\Health\Checks\Checks\EnvironmentCheck::new(),
        ];
    }

    private function configureWorkersCheck(): array
    {
        return [
            \Spatie\Health\Checks\Checks\QueueCheck::new(),
            \Spatie\Health\Checks\Checks\ScheduleCheck::new(),
        ];
    }
    private function configurePerformanceChecks(): array
    {
        return [
            \App\Checks\MemoryUsageCheck::new(),
            \App\Checks\CpuUsageCheck::new(),
            \Spatie\Health\Checks\Checks\UsedDiskSpaceCheck::new(),
            \Spatie\CpuLoadHealthCheck\CpuLoadCheck::new()
                ->failWhenLoadIsHigherInTheLast5Minutes(2)
                ->failWhenLoadIsHigherInTheLast15Minutes(1.5),
        ];
    }

    private function configureSecurityCheck(): array
    {
        return [
            \Spatie\SecurityAdvisoriesHealthCheck\SecurityAdvisoriesCheck::new(),
        ];
    }
}
