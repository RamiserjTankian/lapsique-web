<?php

namespace App\Filament\Widgets;

use App\Services\CustomerJourneyInsightsService;
use Filament\Widgets\ChartWidget;

class CustomerJourneyFunnelChartWidget extends ChartWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'Funnel completo de leads y ventas';

    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $rows = app(CustomerJourneyInsightsService::class)->dashboard()['funnel'];

        return [
            'datasets' => [
                [
                    'label' => 'Clientes / visitantes',
                    'data' => array_column($rows, 'count'),
                    'backgroundColor' => [
                        'rgba(59, 130, 246, 0.72)',
                        'rgba(14, 165, 233, 0.72)',
                        'rgba(16, 185, 129, 0.72)',
                        'rgba(245, 158, 11, 0.72)',
                        'rgba(34, 197, 94, 0.72)',
                    ],
                ],
            ],
            'labels' => array_column($rows, 'stage'),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
