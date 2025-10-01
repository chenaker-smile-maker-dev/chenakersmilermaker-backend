<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

use Spatie\Health\Commands\DispatchQueueCheckJobsCommand;
use Spatie\Health\Commands\ScheduleCheckHeartbeatCommand;
use Spatie\Health\Commands\RunHealthChecksCommand;

Schedule::command(RunHealthChecksCommand::class)->everyMinute();
Schedule::command(DispatchQueueCheckJobsCommand::class)->everyMinute();
Schedule::command(ScheduleCheckHeartbeatCommand::class)->everyMinute();

Schedule::command('auth:clear-resets')->everyMinute();
Schedule::command('queue:prune-batches')->everyMinute();
Schedule::command('queue:prune-failed')->everyMinute();
Schedule::command('sanctum:prune-expired')->everyMinute();
Schedule::command('media-library:clean')->everyMinute();
Schedule::command('activitylog:clean')->everyMinute();

Artisan::command('testing', function () {
    $this->comment("testing");
})->purpose('Display an testing message');
