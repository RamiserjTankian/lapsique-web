<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\InteractsWithMetaAdsPeriod;
use App\Services\Meta\MetaAttributionReportService;
use Filament\Widgets\ChartWidget;

class MetaAdsSpendSalesChartWidget extends ChartWidget
{
    use InteractsWithMetaAdsPeriod;

    protected static bool $isDiscovered = false;

    protected static ?int $sort = 1;

    protected ?string $heading = 'Gasto Meta vs ventas cerradas';

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        try {
            $periodDays = $this->getMetaAdsPeriodDays();
            $since = now()->subDays($periodDays)->startOfDay();
            $until = now()->endOfDay();
            $daily = app(MetaAttributionReportService::class)->report($since, $until)['daily'];
        } catch (\Throwable $e) {
            report($e);

            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Gasto (MXN)',
                    'data' => $daily['spend'],
                    'borderColor' => 'rgb(239, 68, 68)',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.15)',
                    'yAxisID' => 'y',
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Ventas cerradas',
                    'data' => $daily['sales'],
                    'borderColor' => 'rgb(34, 197, 94)',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.15)',
                    'yAxisID' => 'y1',
                    'tension' => 0.3,
                ],
            ],
            'labels' => $daily['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'type' => 'linear',
                    'position' => 'left',
                    'title' => ['display' => true, 'text' => 'Gasto MXN'],
                ],
                'y1' => [
                    'type' => 'linear',
                    'position' => 'right',
                    'grid' => ['drawOnChartArea' => false],
                    'title' => ['display' => true, 'text' => 'Ventas'],
                ],
            ],
        ];
    }
}
