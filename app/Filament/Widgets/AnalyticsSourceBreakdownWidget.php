<?php

namespace App\Filament\Widgets;

use App\Services\AnalyticsInsightsService;
use Filament\Widgets\ChartWidget;

class AnalyticsSourceBreakdownWidget extends ChartWidget
{
    protected static ?int $sort = 4;

    protected ?string $heading = 'Origen del trafico';

    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $rows = app(AnalyticsInsightsService::class)->dashboard()['source_breakdown'];

        return [
            'datasets' => [
                [
                    'label' => 'Sesiones',
                    'data' => array_column($rows, 'sessions'),
                    'backgroundColor' => [
                        'rgba(59, 130, 246, 0.68)',
                        'rgba(16, 185, 129, 0.68)',
                        'rgba(234, 179, 8, 0.68)',
                        'rgba(236, 72, 153, 0.68)',
                        'rgba(107, 114, 128, 0.68)',
                        'rgba(14, 116, 144, 0.68)',
                    ],
                ],
            ],
            'labels' => array_column($rows, 'label'),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
