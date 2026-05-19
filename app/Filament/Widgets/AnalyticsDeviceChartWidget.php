<?php

namespace App\Filament\Widgets;

use App\Models\AnalyticsSession;
use Filament\Widgets\ChartWidget;

class AnalyticsDeviceChartWidget extends ChartWidget
{
    protected static ?int $sort = 5;

    protected ?string $heading = 'Dispositivos';

    protected function getData(): array
    {
        $since = now()->subDays((int) config('analytics.dashboard_days', 30));

        $devices = AnalyticsSession::query()
            ->selectRaw('device_type, COUNT(*) as count')
            ->where('created_at', '>=', $since)
            ->groupBy('device_type')
            ->orderByDesc('count')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Sesiones',
                    'data' => $devices->pluck('count')->all(),
                    'backgroundColor' => [
                        'rgba(59, 130, 246, 0.6)',
                        'rgba(34, 197, 94, 0.6)',
                        'rgba(234, 179, 8, 0.6)',
                        'rgba(156, 163, 175, 0.6)',
                    ],
                ],
            ],
            'labels' => $devices->pluck('device_type')->map(function ($device) {
                return $device ?: 'unknown';
            })->all(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
