<?php

namespace App\Filament\Widgets;

use App\Services\AnalyticsInsightsService;
use Filament\Widgets\ChartWidget;

class AnalyticsTopPagesChartWidget extends ChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'Top paginas';

    protected function getData(): array
    {
        $pages = app(AnalyticsInsightsService::class)->dashboard()['top_pages'];

        return [
            'datasets' => [
                [
                    'label' => 'Pageviews',
                    'data' => array_column($pages, 'pageviews'),
                    'backgroundColor' => 'rgba(34, 197, 94, 0.6)',
                    'borderColor' => 'rgb(34, 197, 94)',
                ],
            ],
            'labels' => array_column($pages, 'path'),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
