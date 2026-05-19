<?php

namespace App\Filament\Widgets;

use App\Support\ContentBookingSalesInsights;
use Filament\Widgets\ChartWidget;

class ContentBookingSalesTimelineWidget extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Reservas confirmadas por día';

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $chart = ContentBookingSalesInsights::dailyConfirmedChart();

        return [
            'datasets' => [
                [
                    'label' => 'Confirmadas',
                    'data' => $chart['data'],
                    'borderColor' => 'rgb(34, 197, 94)',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.2)',
                    'borderWidth' => 2,
                    'fill' => true,
                ],
            ],
            'labels' => $chart['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
