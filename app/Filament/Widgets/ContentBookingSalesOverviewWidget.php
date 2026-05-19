<?php

namespace App\Filament\Widgets;

use App\Support\ContentBookingSalesInsights;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ContentBookingSalesOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $days = ContentBookingSalesInsights::periodDays();
        $stats = ContentBookingSalesInsights::periodStats();
        $byProvider = ContentBookingSalesInsights::revenueByProvider();
        $conversion = ContentBookingSalesInsights::checkoutConversionRate();
        $currency = 'MXN';

        $providerText = collect($byProvider)
            ->map(fn ($count, $provider) => strtoupper((string) $provider).": {$count}")
            ->implode(' · ') ?: 'Sin datos';

        return [
            Stat::make("Ingresos sesiones ({$days}d)", '$'.number_format($stats['revenue'], 0)." {$currency}")
                ->description("{$stats['orders']} reservas confirmadas")
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
            Stat::make('Ticket promedio', '$'.number_format($stats['average'], 0)." {$currency}")
                ->description('Por reserva confirmada')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('primary'),
            Stat::make('Pendientes / fallidas', "{$stats['pending']} / {$stats['failed']}")
                ->description('En el periodo')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
            Stat::make('Conversión checkout', $conversion !== null ? "{$conversion}%" : '—')
                ->description($providerText)
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('info'),
        ];
    }
}
