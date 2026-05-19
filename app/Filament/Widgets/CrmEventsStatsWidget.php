<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use App\Models\GuestListEntry;
use App\Models\GuestListInviteLink;
use App\Models\GuestListScan;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CrmEventsStatsWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $totalEvents = Event::count();
        $upcomingEvents = Event::where('starts_at', '>=', now())->count();
        $guestListEntries = GuestListEntry::count();
        $inviteLinks = GuestListInviteLink::count();
        $scans = GuestListScan::count();

        return [
            Stat::make('Eventos', number_format($totalEvents))
                ->description("{$upcomingEvents} proximos")
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary'),
            Stat::make('Guest List', number_format($guestListEntries))
                ->description('Registros totales')
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->color('success'),
            Stat::make('Invite Links', number_format($inviteLinks))
                ->description('Links activos')
                ->descriptionIcon('heroicon-m-link')
                ->color('info'),
            Stat::make('Scans', number_format($scans))
                ->description('Accesos registrados')
                ->descriptionIcon('heroicon-m-qr-code')
                ->color('warning'),
        ];
    }
}
