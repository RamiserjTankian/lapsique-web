<?php

namespace App\Filament\Widgets;

use App\Models\ContactLog;
use Filament\Widgets\ChartWidget;

class ContactLogStatusChartWidget extends ChartWidget
{
    protected ?string $heading = 'Contact logs por estado';

    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        $since = now()->subDays(30);

        $statuses = ContactLog::query()
            ->selectRaw('status, COUNT(*) as count')
            ->where('created_at', '>=', $since)
            ->groupBy('status')
            ->orderByDesc('count')
            ->get();

        $labels = $statuses->pluck('status')->map(function ($status) {
            return $status ?: 'unknown';
        })->all();

        return [
            'datasets' => [
                [
                    'label' => 'Contact logs',
                    'data' => $statuses->pluck('count')->all(),
                    'backgroundColor' => [
                        'rgba(59, 130, 246, 0.6)',
                        'rgba(34, 197, 94, 0.6)',
                        'rgba(234, 179, 8, 0.6)',
                        'rgba(239, 68, 68, 0.6)',
                        'rgba(148, 163, 184, 0.6)',
                        'rgba(16, 185, 129, 0.6)',
                    ],
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
