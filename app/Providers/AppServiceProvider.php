<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureScramble();

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }


    private function configureScramble(): void
    {
        Scramble::registerApi('v1', [
            'api_path' => 'api/v1',
            'export_path' => 'public/v1.json',
            "ui" => [
                "title" => config('app.name') . " API v1",
                "theme" => "light",
                'hide_schemas' => true,
                // 'logo' => '/favicon.ico',
                'layout' => 'responsive',
            ],
        ])
            ->withDocumentTransformers(function (OpenApi $openApi) {
                $openApi->secure(SecurityScheme::http('bearer'));
            });

        Scramble::registerUiRoute('docs/v1', api: 'v1');
    }
}
