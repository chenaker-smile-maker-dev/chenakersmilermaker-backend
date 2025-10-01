<?php

namespace App\Http\Controllers\Web;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;
use Spatie\Health\Commands\RunHealthChecksCommand;
use Spatie\Health\Health;
use Spatie\Health\ResultStores\ResultStore;

class HealthCheckResultsController
{
    public function __invoke(Request $request, ResultStore $resultStore, Health $health): JsonResponse|View
    {
        if ($request->has('fresh')) {
            Artisan::call(RunHealthChecksCommand::class);
        }

        $checkResults = $resultStore->latestResults();
        $finishedAt = $checkResults?->finishedAt;

        return view('web.pages.health-results', [
            'lastRanAt' => $finishedAt ? new Carbon($finishedAt) : null,
            'checkResults' => $checkResults,
            'assets' => $health->assets(),
            'theme' => config('health.theme'),
        ]);
    }
}
