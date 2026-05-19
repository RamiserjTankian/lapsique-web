<?php

namespace App\Filament\Resources\SalesAnalytics\Widgets;

use App\Models\TicketOrder;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SalesOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $days = (int) config('analytics.dashboard_days', 30);
        $since = now()->subDays($days);
        $currency = config('mercadopago.currency', 'MXN');

        $baseQuery = TicketOrder::query()
            ->where('status', 'paid');

        $periodQuery = (clone $baseQuery)
            ->where('paid_at', '>=', $since);

        $ordersCount = (clone $periodQuery)->count();
        $revenue = (clone $periodQuery)->sum('total');
        $consumible = (clone $periodQuery)->sum('subtotal');
        $serviceFee = (clone $periodQuery)->sum('fee');
        $tickets = (clone $periodQuery)->sum('attendees_expected');
        $registered = (clone $periodQuery)->sum('attendees_registered');
        $average = $ordersCount > 0 ? $revenue / $ordersCount : 0;
        $totalRevenue = (clone $baseQuery)->sum('total');

        return [
            Stat::make("Ingresos últimos {$days} días", number_format($revenue, 2) . " {$currency}")
                ->description('Total histórico ' . number_format($totalRevenue, 2) . " {$currency}")
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
            Stat::make('Consumible vs servicio', number_format($consumible, 2) . " / " . number_format($serviceFee, 2) . " {$currency}")
                ->description('Base y cargo de servicio')
                ->descriptionIcon('heroicon-m-arrows-right-left')
                ->color('gray'),
            Stat::make('Órdenes pagadas', number_format($ordersCount))
                ->description("Desde {$since->format('d M Y')}")
                ->descriptionIcon('heroicon-m-receipt-percent')
                ->color('primary'),
            Stat::make('Tickets vendidos', number_format($tickets))
                ->description(number_format($registered) . ' registrados')
                ->descriptionIcon('heroicon-m-ticket')
                ->color('info'),
            Stat::make('Ticket promedio', number_format($average, 2) . " {$currency}")
                ->description('Por orden')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('warning'),
        ];
    }
}
