<?php

namespace App\Filament\Pages;

use App\Actions\SyncMetaCampaignInsightsAction;
use App\Filament\Widgets\MetaAdsCampaignTableWidget;
use App\Filament\Widgets\MetaAdsOverviewWidget;
use App\Filament\Widgets\MetaAdsSpendSalesChartWidget;
use App\Services\Meta\MetaAttributionReportService;
use App\Services\Meta\MetaMarketingApiClient;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Dashboard;
use UnitEnum;

class MetaAdsPerformanceDashboard extends Dashboard
{
    protected static string $routePath = 'meta-ads-performance';

    protected static ?string $navigationLabel = 'Meta Ads KPI';

    protected static ?string $title = 'Rendimiento Meta Ads';

    protected static UnitEnum|string|null $navigationGroup = 'Reportes';

    protected static ?int $navigationSort = 3;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-megaphone';

    public int $periodDays = 30;

    public function mount(): void
    {
        $period = (int) request()->query('period', config('meta-ads.sync_days_default', 30));
        if (in_array($period, [7, 30, 90], true)) {
            $this->periodDays = $period;
        }

        session(['meta_ads_period_days' => $this->periodDays]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sync')
                ->label('Sincronizar ahora')
                ->icon('heroicon-o-arrow-path')
                ->requiresConfirmation()
                ->modalDescription('Descarga gastos y métricas de campañas desde Meta para el periodo configurado.')
                ->action(function (
                    SyncMetaCampaignInsightsAction $sync,
                    MetaMarketingApiClient $client,
                    MetaAttributionReportService $reports,
                ): void {
                    if (! $client->isConfigured()) {
                        Notification::make()
                            ->title('Meta Ads no configurado')
                            ->body('Activa META_ADS_ENABLED y define META_ACCESS_TOKEN y META_AD_ACCOUNT_ID en .env')
                            ->danger()
                            ->send();

                        return;
                    }

                    try {
                        $result = $sync->execute($this->periodDays);
                        $reports->clearCache();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Error al sincronizar')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                        report($e);

                        return;
                    }

                    Notification::make()
                        ->title('Sincronización completada')
                        ->body("Filas actualizadas: {$result['synced']}")
                        ->success()
                        ->send();

                    $this->dispatch('$refresh');
                }),
            Action::make('period')
                ->label('Periodo')
                ->icon('heroicon-o-calendar-days')
                ->form([
                    Select::make('periodDays')
                        ->label('Rango')
                        ->options([
                            7 => 'Últimos 7 días',
                            30 => 'Últimos 30 días',
                            90 => 'Últimos 90 días',
                        ])
                        ->default($this->periodDays)
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $this->periodDays = (int) $data['periodDays'];
                    session(['meta_ads_period_days' => $this->periodDays]);
                    app(MetaAttributionReportService::class)->clearCache();
                    $this->dispatch('$refresh');
                }),
        ];
    }

    public function getWidgets(): array
    {
        $periodDays = max(1, $this->periodDays);

        return [
            MetaAdsOverviewWidget::make(['periodDays' => $periodDays]),
            MetaAdsSpendSalesChartWidget::make(['periodDays' => $periodDays]),
            MetaAdsCampaignTableWidget::make(['periodDays' => $periodDays]),
        ];
    }
}
