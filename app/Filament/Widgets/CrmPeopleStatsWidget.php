<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\Rp;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CrmPeopleStatsWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $recentDays = 30;
        $since = now()->subDays($recentDays);

        $totalCustomers = Customer::count();
        $newCustomers = Customer::where('created_at', '>=', $since)->count();
        $totalRps = Rp::count();
        $totalUsers = User::count();

        return [
            Stat::make('Clientes', number_format($totalCustomers))
                ->description("{$newCustomers} nuevos en {$recentDays} dias")
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),
            Stat::make('RPs', number_format($totalRps))
                ->description('Relaciones publicas')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('info'),
            Stat::make('Usuarios', number_format($totalUsers))
                ->description('Accesos al panel')
                ->descriptionIcon('heroicon-m-shield-check')
                ->color('success'),
            Stat::make('Newsletter', number_format(Customer::where('subscribed_newsletter', true)->count()))
                ->description('Suscritos activos')
                ->descriptionIcon('heroicon-m-envelope')
                ->color('warning'),
        ];
    }
}
