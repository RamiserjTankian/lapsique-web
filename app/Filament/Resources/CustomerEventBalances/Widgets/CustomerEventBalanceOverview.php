<?php

namespace App\Filament\Resources\CustomerEventBalances\Widgets;

use App\Filament\Resources\CustomerEventBalances\Pages\ListCustomerEventBalances;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CustomerEventBalanceOverview extends BaseWidget
{
    use InteractsWithPageTable;

    protected static ?int $sort = 0;

    protected int | string | array $columnSpan = 'full';

    protected function getTablePage(): string
    {
        return ListCustomerEventBalances::class;
    }

    protected function getStats(): array
    {
        $query = $this->getPageTableQuery();
        $currency = (clone $query)->value('currency') ?? config('mercadopago.currency', 'MXN');
        $paidCustomers = (clone $query)->count();
        $pendingBalance = round((float) ((clone $query)->sum('balance')), 2);
        $consumedBalance = round((float) ((clone $query)->sum('total_consumed')), 2);
        $creditedBalance = round((float) ((clone $query)->sum('total_credited')), 2);

        return [
            Stat::make('Clientes que pagaron', number_format($paidCustomers))
                ->description('Clientes con saldo acreditado en el filtro actual')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
            Stat::make('Saldo pendiente', number_format($pendingBalance, 2) . " {$currency}")
                ->description('Saldo acreditado total: ' . number_format($creditedBalance, 2) . " {$currency}")
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('warning'),
            Stat::make('Saldo consumido', number_format($consumedBalance, 2) . " {$currency}")
                ->description('Consumos POS registrados en el filtro actual')
                ->descriptionIcon('heroicon-m-fire')
                ->color('danger'),
        ];
    }
}
