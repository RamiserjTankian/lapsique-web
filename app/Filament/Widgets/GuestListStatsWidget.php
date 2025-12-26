<?php

namespace App\Filament\Widgets;

use App\Models\GuestListEntry;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class GuestListStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalEntries = GuestListEntry::count();
        $men = GuestListEntry::where('gender', 'male')->count();
        $women = GuestListEntry::where('gender', 'female')->count();
        $confirmed = GuestListEntry::where('status', 'confirmed')->count();
        $attended = GuestListEntry::where('status', 'attended')->count();
        $pending = GuestListEntry::where('status', 'pending')->count();
        $totalPlusOnes = GuestListEntry::sum('plus_ones') ?? 0;
        $totalWithPlusOnes = $totalEntries + $totalPlusOnes;

        return [
            Stat::make('Total Invitados', number_format($totalEntries))
                ->description('Incluyendo ' . number_format($totalPlusOnes) . ' acompañantes')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary')
                ->chart([$totalEntries]),

            Stat::make('Hombres', number_format($men))
                ->description(($totalEntries > 0 ? round(($men / $totalEntries) * 100, 1) : 0) . '% del total')
                ->descriptionIcon('heroicon-m-user')
                ->color('info'),

            Stat::make('Mujeres', number_format($women))
                ->description(($totalEntries > 0 ? round(($women / $totalEntries) * 100, 1) : 0) . '% del total')
                ->descriptionIcon('heroicon-m-user')
                ->color('success'),

            Stat::make('Confirmados', number_format($confirmed))
                ->description(($totalEntries > 0 ? round(($confirmed / $totalEntries) * 100, 1) : 0) . '% de confirmación')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Asistieron', number_format($attended))
                ->description(($totalEntries > 0 ? round(($attended / $totalEntries) * 100, 1) : 0) . '% de asistencia')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),

            Stat::make('Pendientes', number_format($pending))
                ->description('Esperando confirmación')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
        ];
    }
}
