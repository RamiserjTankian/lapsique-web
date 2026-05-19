<?php

namespace App\Filament\Widgets;

use App\Services\AnalyticsInsightsService;
use Filament\Widgets\Widget;

class AnalyticsDeepDiveWidget extends Widget
{
    protected static ?int $sort = 7;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.analytics-deep-dive-widget';

    protected function getViewData(): array
    {
        $snapshot = app(AnalyticsInsightsService::class)->dashboard();

        return [
            'sources' => $snapshot['source_breakdown'],
            'locations' => $snapshot['top_locations'],
            'events' => $snapshot['top_events'],
            'sessions' => collect($snapshot['recent_sessions'])->all(),
        ];
    }
}
