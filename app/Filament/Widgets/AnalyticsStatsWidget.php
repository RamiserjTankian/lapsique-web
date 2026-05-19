<?php

namespace App\Filament\Widgets;

use App\Services\AnalyticsInsightsService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AnalyticsStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $days = (int) config('analytics.dashboard_days', 30);
        $snapshot = app(AnalyticsInsightsService::class)->dashboard($days);
        $stats = $snapshot['stats'];

        return [
            Stat::make('Sesiones', number_format((int) $stats['sessions']))
                ->description("Ultimos {$days} dias")
                ->descriptionIcon('heroicon-m-sparkles')
                ->color('primary'),
            Stat::make('Visitas totales', number_format((int) $stats['pageviews']))
                ->description("{$stats['pages_per_session']} por sesion")
                ->descriptionIcon('heroicon-m-document-text')
                ->color('success'),
            Stat::make('Visitantes unicos', number_format((int) $stats['unique_visitors']))
                ->description(number_format((int) $stats['new_visitors']) . ' nuevos')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),
            Stat::make('Visitantes recurrentes', number_format((int) $stats['repeat_visitors']))
                ->description(number_format((int) $stats['repeat_pageviews']) . ' visitas repetidas')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('warning'),
            Stat::make('Eventos activados', number_format((int) $stats['events']))
                ->description("{$stats['events_per_session']} por sesion")
                ->descriptionIcon('heroicon-m-bolt')
                ->color('gray'),
            Stat::make('Ordenes pagadas', number_format((int) $stats['paid_orders']))
                ->description(number_format((float) $stats['sales_conversion_rate'], 2) . '% conversion')
                ->descriptionIcon('heroicon-m-credit-card')
                ->color('success'),
            Stat::make('Ingresos', number_format((float) $stats['revenue'], 2) . ' MXN')
                ->description(number_format((int) $stats['paid_orders_attributed']) . ' ventas atribuidas')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),
            Stat::make('Guest list', number_format((int) $stats['guestlist_registrations']))
                ->description('Registros en el periodo')
                ->descriptionIcon('heroicon-m-cursor-arrow-rays')
                ->color('info'),
        ];
    }
}
