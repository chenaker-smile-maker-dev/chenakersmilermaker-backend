<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;

class ScrambleServiceProvider extends ServiceProvider
{

    public function register(): void {}

    public function boot(): void
    {
        $this->configureScramble();
    }

    private function configureScramble(): void
    {
        Scramble::registerApi('v1', [
            'api_path' => 'api/v1',
            'export_path' => 'public/v1.json',
            "ui" => [
                "title" => config('app.name') . " API v1",
                "theme" => "dark",
                'hide_schemas' => true,
                'logo' => asset('favicon.svg'),
                'layout' => 'responsive',
            ],
        ])
            ->withDocumentTransformers(function (OpenApi $openApi) {
                $openApi->secure(SecurityScheme::http('bearer'));
            });

        Scramble::registerUiRoute('docs/v1', api: 'v1');
    }
}
