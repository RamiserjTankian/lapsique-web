<?php

namespace App\Filament\Widgets;

use App\Models\AnalyticsEvent;
use Filament\Widgets\ChartWidget;

class AnalyticsTopClicksChartWidget extends ChartWidget
{
    protected static ?int $sort = 6;

    protected ?string $heading = 'Top clicks';

    protected function getData(): array
    {
        $since = now()->subDays((int) config('analytics.dashboard_days', 30));

        $clicks = AnalyticsEvent::query()
            ->selectRaw('COALESCE(element_href, label) as target, COUNT(*) as count')
            ->where('created_at', '>=', $since)
            ->where('name', 'click')
            ->where(function ($query) {
                $query->whereNotNull('element_href')
                    ->orWhereNotNull('label');
            })
            ->groupBy('target')
            ->orderByDesc('count')
            ->limit(8)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Clicks',
                    'data' => $clicks->pluck('count')->all(),
                    'backgroundColor' => 'rgba(236, 72, 153, 0.6)',
                    'borderColor' => 'rgb(236, 72, 153)',
                ],
            ],
            'labels' => $clicks->pluck('target')->map(function ($label) {
                return $label ?: 'sin etiqueta';
            })->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
