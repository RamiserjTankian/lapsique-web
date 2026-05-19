<?php

namespace App\Filament\Widgets;

use App\Services\AnalyticsInsightsService;
use Filament\Widgets\ChartWidget;

class AnalyticsTrafficChartWidget extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Pageviews por dia';

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $rows = app(AnalyticsInsightsService::class)->dashboard()['daily_traffic'];

        return [
            'datasets' => [
                [
                    'label' => 'Pageviews',
                    'data' => array_column($rows, 'pageviews'),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 2,
                    'tension' => 0.35,
                ],
                [
                    'label' => 'Visitantes unicos',
                    'data' => array_column($rows, 'unique_visitors'),
                    'backgroundColor' => 'rgba(16, 185, 129, 0.12)',
                    'borderColor' => 'rgb(16, 185, 129)',
                    'borderWidth' => 2,
                    'tension' => 0.35,
                ],
                [
                    'label' => 'Ordenes pagadas',
                    'data' => array_column($rows, 'paid_orders'),
                    'backgroundColor' => 'rgba(234, 179, 8, 0.18)',
                    'borderColor' => 'rgb(234, 179, 8)',
                    'borderWidth' => 2,
                    'tension' => 0.35,
                ],
            ],
            'labels' => array_column($rows, 'label'),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
