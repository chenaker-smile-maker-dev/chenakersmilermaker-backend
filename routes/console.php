<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

use Spatie\Health\Commands\DispatchQueueCheckJobsCommand;
use Spatie\Health\Commands\ScheduleCheckHeartbeatCommand;
use Spatie\Health\Commands\RunHealthChecksCommand;

Schedule::command(RunHealthChecksCommand::class)->everyFiveMinutes();
Schedule::command(DispatchQueueCheckJobsCommand::class)->everyFiveMinutes();
Schedule::command(ScheduleCheckHeartbeatCommand::class)->everyFiveMinutes();

Schedule::command('auth:clear-resets')->daily();
Schedule::command('queue:prune-batches')->daily();
Schedule::command('queue:prune-failed')->daily();
Schedule::command('sanctum:prune-expired')->daily();
Schedule::command('media-library:clean')->daily();
Schedule::command('activitylog:clean')->daily();
