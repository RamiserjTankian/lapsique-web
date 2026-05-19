<?php

namespace App\Filament\Resources\SalesAnalytics\Widgets;

use App\Filament\Resources\SalesAnalytics\Concerns\InteractsWithSalesAnalyticsRecord;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EventSalesOverviewWidget extends BaseWidget
{
    use InteractsWithSalesAnalyticsRecord;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $insights = $this->getSalesInsights();

        if (! $insights) {
            return [];
        }

        $summary = $insights->summary();
        $currency = $this->getSalesAnalyticsRecord()?->currency ?? 'MXN';

        return [
            Stat::make('Clientes que entran', number_format($summary['entry_visitors']))
                ->description(number_format($summary['entry_pageviews']) . ' visitas en ' . number_format($summary['entry_sessions']) . ' sesiones')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),
            Stat::make('Ven accesos', number_format($summary['ticket_visitors']))
                ->description(number_format($summary['ticket_section_rate'], 1) . '% de los que entran')
                ->descriptionIcon('heroicon-m-ticket')
                ->color('info'),
            Stat::make('Preparan compra', number_format($summary['cart_visitors']))
                ->description(number_format($summary['cart_rate'], 1) . '% de los que entran')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('warning'),
            Stat::make('Inician checkout', number_format($summary['checkout_started_visitors']))
                ->description(number_format($summary['checkout_started_rate'], 1) . '% de los que entran')
                ->descriptionIcon('heroicon-m-credit-card')
                ->color('gray'),
            Stat::make('Clientes que pagan', number_format($summary['paid_customers']))
                ->description(number_format($summary['paid_orders']) . ' órdenes | ' . number_format($summary['visitor_to_paid_rate'], 1) . '% conversión')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
            Stat::make('Ingresos', number_format($summary['revenue_total'], 2) . ' ' . $currency)
                ->description(number_format($summary['tickets_sold']) . ' tickets vendidos')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('success'),
        ];
    }
}
