<?php

namespace App\Filament\Resources\SessionCustomers\Widgets;

use App\Filament\Resources\SessionCustomers\Concerns\InteractsWithSessionCustomerRecord;
use App\Support\SessionCustomerInsights;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SessionCustomerProfileStatsWidget extends BaseWidget
{
    use InteractsWithSessionCustomerRecord;

    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $customer = $this->getSessionCustomerRecord();

        if (! $customer) {
            return [];
        }

        $stats = SessionCustomerInsights::profileStats($customer);

        return [
            Stat::make('Sesiones pagadas', (string) $stats['confirmed'])
                ->description("{$stats['bookings']} reservas en total")
                ->color('primary'),
            Stat::make('Lifetime value', '$'.number_format($stats['revenue'], 0).' MXN')
                ->description('Ingresos de contenido confirmados')
                ->color('success'),
            Stat::make('Entregas pendientes', (string) $stats['pending_delivery'])
                ->description($stats['pending_delivery'] > 0 ? 'Requiere subir o publicar material' : 'Al día')
                ->color($stats['pending_delivery'] > 0 ? 'warning' : 'success'),
            Stat::make('RFC', $customer->fiscal_rfc ?: 'Sin registrar')
                ->description($customer->fiscal_legal_name ?: 'Completa datos fiscales para facturar')
                ->color($customer->fiscal_rfc ? 'gray' : 'danger'),
        ];
    }
}
