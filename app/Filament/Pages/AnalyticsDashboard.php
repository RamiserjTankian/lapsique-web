<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AnalyticsDeepDiveWidget;
use App\Filament\Widgets\AnalyticsDeviceChartWidget;
use App\Filament\Widgets\AnalyticsHourlyActivityChartWidget;
use App\Filament\Widgets\AnalyticsRealtimeWidget;
use App\Filament\Widgets\AnalyticsSourceBreakdownWidget;
use App\Filament\Widgets\AnalyticsStatsWidget;
use App\Filament\Widgets\AnalyticsTopPagesChartWidget;
use App\Filament\Widgets\AnalyticsTrafficChartWidget;
use BackedEnum;
use Filament\Pages\Dashboard;
use UnitEnum;

class AnalyticsDashboard extends Dashboard
{
    protected static string $routePath = 'analytics';

    protected static ?string $navigationLabel = 'Analítica web';

    protected static ?string $title = 'Analítica del sitio';

    protected static UnitEnum|string|null $navigationGroup = 'Reportes';

    protected static ?int $navigationSort = 0;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-chart-bar';

    public function getWidgets(): array
    {
        return [
            AnalyticsStatsWidget::class,
            AnalyticsRealtimeWidget::class,
            AnalyticsTrafficChartWidget::class,
            AnalyticsHourlyActivityChartWidget::class,
            AnalyticsSourceBreakdownWidget::class,
            AnalyticsTopPagesChartWidget::class,
            AnalyticsDeviceChartWidget::class,
            AnalyticsDeepDiveWidget::class,
        ];
    }
}
