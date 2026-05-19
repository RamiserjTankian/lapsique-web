<?php

namespace App\Filament\Widgets;

use App\Models\AnalyticsSession;
use Filament\Widgets\ChartWidget;

class AnalyticsTopReferrersChartWidget extends ChartWidget
{
    protected static ?int $sort = 4;

    protected ?string $heading = 'Top referrers';

    protected function getData(): array
    {
        $since = now()->subDays((int) config('analytics.dashboard_days', 30));

        $referrers = AnalyticsSession::query()
            ->selectRaw('referrer_domain, COUNT(*) as count')
            ->where('created_at', '>=', $since)
            ->whereNotNull('referrer_domain')
            ->groupBy('referrer_domain')
            ->orderByDesc('count')
            ->limit(6)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Sesiones',
                    'data' => $referrers->pluck('count')->all(),
                    'backgroundColor' => [
                        'rgba(59, 130, 246, 0.6)',
                        'rgba(16, 185, 129, 0.6)',
                        'rgba(234, 179, 8, 0.6)',
                        'rgba(239, 68, 68, 0.6)',
                        'rgba(168, 85, 247, 0.6)',
                        'rgba(14, 116, 144, 0.6)',
                    ],
                ],
            ],
            'labels' => $referrers->pluck('referrer_domain')->all(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
