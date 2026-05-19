<?php

namespace App\Filament\Widgets;

use App\Models\GuestListEntry;
use Filament\Widgets\ChartWidget;

class GuestListStatusChartWidget extends ChartWidget
{
    protected ?string $heading = 'Guest list por estado';

    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        $since = now()->subDays(30);

        $statuses = GuestListEntry::query()
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
                    'label' => 'Registros',
                    'data' => $statuses->pluck('count')->all(),
                    'backgroundColor' => [
                        'rgba(59, 130, 246, 0.6)',
                        'rgba(34, 197, 94, 0.6)',
                        'rgba(234, 179, 8, 0.6)',
                        'rgba(239, 68, 68, 0.6)',
                        'rgba(168, 85, 247, 0.6)',
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
