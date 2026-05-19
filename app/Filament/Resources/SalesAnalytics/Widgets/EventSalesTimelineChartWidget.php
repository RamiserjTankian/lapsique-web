<?php

namespace App\Filament\Resources\SalesAnalytics\Widgets;

use App\Filament\Resources\SalesAnalytics\Concerns\InteractsWithSalesAnalyticsRecord;
use Filament\Widgets\ChartWidget;

class EventSalesTimelineChartWidget extends ChartWidget
{
    use InteractsWithSalesAnalyticsRecord;

    protected ?string $heading = 'Timeline de tráfico y ventas';

    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $insights = $this->getSalesInsights();

        if (! $insights) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        $timeline = $insights->timeline();

        return [
            'datasets' => [
                [
                    'label' => 'Entran',
                    'data' => $timeline['visitors'],
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.18)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Carrito',
                    'data' => $timeline['cart'],
                    'borderColor' => 'rgb(249, 115, 22)',
                    'backgroundColor' => 'rgba(249, 115, 22, 0.18)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Checkout',
                    'data' => $timeline['checkout'],
                    'borderColor' => 'rgb(168, 85, 247)',
                    'backgroundColor' => 'rgba(168, 85, 247, 0.18)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Pagan',
                    'data' => $timeline['paid'],
                    'borderColor' => 'rgb(34, 197, 94)',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.18)',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $timeline['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
