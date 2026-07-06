<?php

namespace App\Filament\Widgets;

use App\Models\ContactLog;
use App\Models\Customer;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LeadManagementStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $since = now()->subDays((int) config('analytics.dashboard_days', 30));

        $newLeads = Customer::query()
            ->where('created_at', '>=', $since)
            ->whereIn('status', ['lead', 'prospect'])
            ->count();

        $hotLeads = Customer::query()
            ->where('lead_score', '>=', 50)
            ->whereIn('status', ['lead', 'prospect'])
            ->count();

        $pendingFollowUp = Customer::query()
            ->where('metadata->follow_up_status', 'pending_follow_up')
            ->count();

        $whatsappReady = Customer::query()
            ->where('subscribed_whatsapp', true)
            ->where(function ($query): void {
                $query->whereNotNull('whatsapp')
                    ->orWhereNotNull('phone');
            })
            ->count();

        $customers = Customer::query()
            ->where('status', 'customer')
            ->count();

        $contacts = ContactLog::query()
            ->where('created_at', '>=', $since)
            ->count();

        return [
            Stat::make('Leads nuevos', number_format($newLeads))
                ->description('Leads/prospectos del periodo')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('info'),
            Stat::make('Leads calientes', number_format($hotLeads))
                ->description('Score 50+ listos para seguimiento')
                ->descriptionIcon('heroicon-m-fire')
                ->color('warning'),
            Stat::make('Seguimientos pendientes', number_format($pendingFollowUp))
                ->description('Marcados desde el panel')
                ->descriptionIcon('heroicon-m-flag')
                ->color($pendingFollowUp > 0 ? 'danger' : 'success'),
            Stat::make('WhatsApp habilitado', number_format($whatsappReady))
                ->description('Contactables por WhatsApp')
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->color('success'),
            Stat::make('Clientes', number_format($customers))
                ->description('Status customer')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('primary'),
            Stat::make('Contactos registrados', number_format($contacts))
                ->description('Logs del periodo')
                ->descriptionIcon('heroicon-m-envelope')
                ->color('gray'),
        ];
    }
}
