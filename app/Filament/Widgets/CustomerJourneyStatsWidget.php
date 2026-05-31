<?php

namespace App\Filament\Widgets;

use App\Services\CustomerJourneyInsightsService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CustomerJourneyStatsWidget extends BaseWidget
{
    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $snapshot = app(CustomerJourneyInsightsService::class)->dashboard();
        $stats = $snapshot['stats'];
        $totalRevenue = (float) $stats['ticket_revenue'] + (float) $stats['booking_revenue'];
        $visitorToLead = $stats['visitors'] > 0 ? round(($stats['identified_leads'] / $stats['visitors']) * 100, 1) : 0;
        $leadToPaid = $stats['identified_leads'] > 0 ? round(($stats['paid_customers'] / $stats['identified_leads']) * 100, 1) : 0;

        return [
            Stat::make('Visitantes -> leads', number_format((int) $stats['identified_leads']))
                ->description(number_format((int) $stats['visitors'])." visitantes · {$visitorToLead}% lead rate")
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('primary'),
            Stat::make('Clientes con intención', number_format((int) $stats['checkout_customers']))
                ->description(number_format((int) $stats['pending_payments']).' pagos pendientes')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('warning'),
            Stat::make('Clientes que pagan', number_format((int) $stats['paid_customers']))
                ->description("{$leadToPaid}% de leads identificados")
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),
            Stat::make('Ingresos atribuidos', '$'.number_format($totalRevenue, 0).' MXN')
                ->description('Tickets $'.number_format((float) $stats['ticket_revenue'], 0).' · Bookings $'.number_format((float) $stats['booking_revenue'], 0))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
            Stat::make('POS consumido', '$'.number_format((float) $stats['pos_consumed'], 0).' MXN')
                ->description(number_format((int) $stats['guestlist_registrations']).' registros guest list')
                ->descriptionIcon('heroicon-m-qr-code')
                ->color('info'),
            Stat::make('Riesgos', number_format((int) $stats['failed_payments']))
                ->description(number_format((int) $stats['repeat_customers']).' clientes recurrentes')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),
        ];
    }
}
