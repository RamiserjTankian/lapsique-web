<?php

namespace App\Filament\Widgets;

use App\Services\AnalyticsInsightsService;
use Filament\Widgets\ChartWidget;

class AnalyticsHourlyActivityChartWidget extends ChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'Horas con mas actividad';

    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $rows = app(AnalyticsInsightsService::class)->dashboard()['hourly_activity'];

        return [
            'datasets' => [
                [
                    'label' => 'Visitas',
                    'data' => array_column($rows, 'pageviews'),
                    'backgroundColor' => 'rgba(14, 165, 233, 0.6)',
                    'borderColor' => 'rgb(14, 165, 233)',
                ],
                [
                    'label' => 'Sesiones',
                    'data' => array_column($rows, 'sessions'),
                    'backgroundColor' => 'rgba(244, 114, 182, 0.28)',
                    'borderColor' => 'rgb(244, 114, 182)',
                ],
            ],
            'labels' => array_column($rows, 'label'),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
