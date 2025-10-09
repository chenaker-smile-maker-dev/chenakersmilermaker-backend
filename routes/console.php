<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

use Spatie\Health\Commands\DispatchQueueCheckJobsCommand;
use Spatie\Health\Commands\ScheduleCheckHeartbeatCommand;
use Spatie\Health\Commands\RunHealthChecksCommand;

Schedule::command(RunHealthChecksCommand::class)->everyMinute();
Schedule::command(DispatchQueueCheckJobsCommand::class)->everyMinute();
Schedule::command(ScheduleCheckHeartbeatCommand::class)->everyMinute();

Schedule::command('auth:clear-resets')->daily();
Schedule::command('queue:prune-batches')->daily();
Schedule::command('queue:prune-failed')->daily();
Schedule::command('sanctum:prune-expired')->daily();
Schedule::command('media-library:clean')->daily();
Schedule::command('activitylog:clean')->daily();

Artisan::command('testing', function () {
    $this->comment("testing");
})->purpose('Display an testing message');
