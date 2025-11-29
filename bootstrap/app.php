<?php

use App\Enums\Api\TokenAbility;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: [
            __DIR__.'/../routes/api.php',
            // __DIR__ . '/../routes/api/v1.php',
        ],
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        $middleware->alias([
            'access' => CheckForAnyAbility::class.':'.TokenAbility::ACCESS_API->value,
            'refresh' => CheckForAnyAbility::class.':'.TokenAbility::REFRESH_ACCESS_TOKEN->value,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
