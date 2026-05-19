<?php

namespace App\Filament\Resources\SalesAnalytics\Widgets;

use App\Filament\Resources\SalesAnalytics\Concerns\InteractsWithSalesAnalyticsRecord;
use Filament\Widgets\Widget;

class EventSalesBreakdownWidget extends Widget
{
    use InteractsWithSalesAnalyticsRecord;

    protected string $view = 'filament.resources.sales-analytics.widgets.event-sales-breakdown-widget';

    protected int|string|array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $insights = $this->getSalesInsights();

        if (! $insights) {
            return [
                'summary' => [],
                'funnelRows' => [],
                'sourceRows' => collect(),
                'deviceRows' => collect(),
                'referrerRows' => collect(),
            ];
        }

        return [
            'summary' => $insights->summary(),
            'funnelRows' => $insights->funnelRows(),
            'sourceRows' => $insights->sourceBreakdown(),
            'deviceRows' => $insights->deviceBreakdown(),
            'referrerRows' => $insights->referrerBreakdown(),
        ];
    }
}
