<?php

namespace App\Filament\Resources\BookingLandingAnalytics\Widgets;

use App\Filament\Resources\BookingLandingAnalytics\Pages\ListBookingLandingAnalytics;
use App\Services\BookingLandingAnalyticsService;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageTable;

class BookingLandingFunnelChartWidget extends ChartWidget
{
    use InteractsWithPageTable;

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Embudo de la landing';

    protected function getTablePage(): string
    {
        return ListBookingLandingAnalytics::class;
    }

    protected function getData(): array
    {
        $funnel = app(BookingLandingAnalyticsService::class)
            ->snapshotForQuery($this->getPageTableQuery())['funnel'];

        return [
            'datasets' => [
                [
                    'label' => 'Sesiones',
                    'data' => array_column($funnel, 'sessions'),
                    'backgroundColor' => [
                        'rgba(148, 163, 184, 0.65)',
                        'rgba(59, 130, 246, 0.65)',
                        'rgba(34, 197, 94, 0.65)',
                        'rgba(6, 182, 212, 0.65)',
                        'rgba(245, 158, 11, 0.65)',
                        'rgba(249, 115, 22, 0.65)',
                        'rgba(16, 185, 129, 0.65)',
                        'rgba(20, 184, 166, 0.65)',
                        'rgba(99, 102, 241, 0.65)',
                        'rgba(234, 179, 8, 0.65)',
                        'rgba(239, 68, 68, 0.65)',
                        'rgba(34, 197, 94, 0.8)',
                    ],
                    'borderColor' => 'rgba(15, 23, 42, 0.65)',
                ],
            ],
            'labels' => array_column($funnel, 'label'),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
