<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\InteractsWithMetaAdsPeriod;
use App\Services\Meta\MetaAttributionReportService;
use App\Services\Meta\MetaMarketingApiClient;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MetaAdsOverviewWidget extends BaseWidget
{
    use InteractsWithMetaAdsPeriod;

    protected static bool $isDiscovered = false;

    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        try {
            return $this->buildStats();
        } catch (\Throwable $e) {
            report($e);

            return [
                Stat::make('Meta Ads KPI', 'Error al cargar')
                    ->description($e->getMessage())
                    ->descriptionIcon('heroicon-m-exclamation-triangle')
                    ->color('danger'),
            ];
        }
    }

    protected function buildStats(): array
    {
        $periodDays = $this->getMetaAdsPeriodDays();
        $since = now()->subDays($periodDays)->startOfDay();
        $until = now()->endOfDay();

        $report = app(MetaAttributionReportService::class)->report($since, $until);
        $summary = $report['summary'];
        $configured = app(MetaMarketingApiClient::class)->isConfigured();
        $lastSync = $report['last_synced_at']
            ? \Illuminate\Support\Carbon::parse($report['last_synced_at'])->diffForHumans()
            : 'Nunca';

        return [
            Stat::make("Gasto Meta ({$periodDays}d)", $configured ? '$'.number_format((float) $summary['spend'], 2) : '—')
                ->description($configured ? "Última sync: {$lastSync}" : 'API no configurada')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('danger'),
            Stat::make('Leads totales', number_format((int) $summary['leads']))
                ->description('Popup + reservas + boletos iniciados')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('info'),
            Stat::make('Ventas cerradas', number_format((int) $summary['sales_closed']))
                ->description('$'.number_format((float) $summary['revenue'], 0).' MXN revenue')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),
            Stat::make('CPL', $summary['cpl'] !== null ? '$'.number_format((float) $summary['cpl'], 2) : '—')
                ->description('Costo por lead')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('warning'),
            Stat::make('CPA', $summary['cpa'] !== null ? '$'.number_format((float) $summary['cpa'], 2) : '—')
                ->description('Costo por venta cerrada')
                ->descriptionIcon('heroicon-m-receipt-percent')
                ->color('warning'),
            Stat::make('ROAS', $summary['roas'] !== null ? number_format((float) $summary['roas'], 2).'x' : '—')
                ->description($summary['lead_to_sale_rate'] !== null ? "{$summary['lead_to_sale_rate']}% lead → venta" : 'Sin conversión')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary'),
        ];
    }
}
