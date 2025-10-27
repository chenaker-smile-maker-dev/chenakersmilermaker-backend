<?php

namespace App\Filament\Admin\Widgets;

use Carbon\Carbon;
use Filament\Widgets\Widget;
use Spatie\Health\ResultStores\ResultStore;

class HealthCheckResultsWidget extends Widget
{
    protected string $view = 'filament.admin.widgets.health-check-results-widget';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 2;

    public function getCheckResults(): array
    {
        $resultStore = app(ResultStore::class);
        $checkResults = $resultStore->latestResults();

        $results = collect($checkResults?->storedCheckResults ?? [])
            ->map(function ($result) {
                return [
                    'name' => $result->name,
                    'label' => $result->label,
                    'notificationMessage' => $result->notificationMessage,
                    'shortSummary' => $result->shortSummary,
                    'status' => $result->status,
                    'meta' => $result->meta,
                ];
            });

        return [
            'results' => $results,
            'finishedAt' => $checkResults?->finishedAt ? new Carbon($checkResults->finishedAt) : null,
        ];
    }
}
