<?php

namespace App\Filament\Resources\SessionCustomers\Widgets;

use App\Filament\Resources\SessionCustomers\Pages\ListSessionCustomers;
use App\Models\ContentBooking;
use App\Models\Customer;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SessionCustomersOverviewWidget extends BaseWidget
{
    use InteractsWithPageTable;

    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    protected function getTablePage(): string
    {
        return ListSessionCustomers::class;
    }

    protected function getStats(): array
    {
        $customers = Customer::query()->whereHas('contentBookings')->count();
        $revenue = (int) ContentBooking::query()->where('status', 'confirmed')->sum('amount');
        $pendingDelivery = ContentBooking::query()
            ->where('status', 'confirmed')
            ->whereNull('deliverables_ready_at')
            ->count();
        $missingFiscal = Customer::query()
            ->whereHas('contentBookings')
            ->where(fn ($q) => $q->whereNull('fiscal_rfc')->orWhere('fiscal_rfc', ''))
            ->count();

        return [
            Stat::make('Clientes con sesión', number_format($customers))
                ->description('Con al menos una reserva de contenido')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),
            Stat::make('Ingresos confirmados', '$'.number_format($revenue, 0).' MXN')
                ->description('Todas las sesiones pagadas')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
            Stat::make('Entregas pendientes', number_format($pendingDelivery))
                ->description('Sesiones sin publicar en portal')
                ->descriptionIcon('heroicon-m-folder-open')
                ->color($pendingDelivery > 0 ? 'warning' : 'success'),
            Stat::make('Sin datos fiscales', number_format($missingFiscal))
                ->description('Clientes activos sin RFC')
                ->descriptionIcon('heroicon-m-document-text')
                ->color($missingFiscal > 0 ? 'danger' : 'gray'),
        ];
    }
}
