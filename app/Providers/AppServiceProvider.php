<?php

namespace App\Providers;

use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Bind {notification} route parameter to Laravel's DatabaseNotification model.
        // This replaces the old PatientNotification model binding.
        Route::model('notification', DatabaseNotification::class);
    }
}
