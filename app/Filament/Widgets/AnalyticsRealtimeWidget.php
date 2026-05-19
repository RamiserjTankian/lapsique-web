<?php

namespace App\Filament\Widgets;

use App\Services\AnalyticsRealtimeService;
use Filament\Widgets\Widget;

class AnalyticsRealtimeWidget extends Widget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.analytics-realtime-widget';

    protected function getViewData(): array
    {
        return app(AnalyticsRealtimeService::class)->snapshot();
    }
}
