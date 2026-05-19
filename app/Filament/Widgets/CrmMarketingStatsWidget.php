<?php

namespace App\Filament\Widgets;

use App\Models\Automation;
use App\Models\Campaign;
use App\Models\ContactLog;
use App\Models\EmailTracking;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CrmMarketingStatsWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $totalCampaigns = Campaign::count();
        $totalAutomations = Automation::count();
        $totalLogs = ContactLog::count();
        $totalTrackings = EmailTracking::count();

        return [
            Stat::make('Campanas', number_format($totalCampaigns))
                ->description('Email marketing')
                ->descriptionIcon('heroicon-m-megaphone')
                ->color('primary'),
            Stat::make('Automations', number_format($totalAutomations))
                ->description('Flujos activos')
                ->descriptionIcon('heroicon-m-cpu-chip')
                ->color('info'),
            Stat::make('Contact Logs', number_format($totalLogs))
                ->description('Historial de contacto')
                ->descriptionIcon('heroicon-m-inbox-stack')
                ->color('success'),
            Stat::make('Email Trackings', number_format($totalTrackings))
                ->description('Opens + clicks')
                ->descriptionIcon('heroicon-m-eye')
                ->color('warning'),
        ];
    }
}
