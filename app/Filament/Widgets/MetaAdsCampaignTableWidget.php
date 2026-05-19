<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\InteractsWithMetaAdsPeriod;
use App\Services\Meta\MetaAttributionReportService;
use Filament\Widgets\Widget;

class MetaAdsCampaignTableWidget extends Widget
{
    use InteractsWithMetaAdsPeriod;

    protected static bool $isDiscovered = false;

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.meta-ads-campaign-table';

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $periodDays = $this->getMetaAdsPeriodDays();

        try {
            $since = now()->subDays($periodDays)->startOfDay();
            $until = now()->endOfDay();
            $report = app(MetaAttributionReportService::class)->report($since, $until);

            return [
                'campaigns' => $report['campaigns'],
                'periodDays' => $periodDays,
                'loadError' => null,
            ];
        } catch (\Throwable $e) {
            report($e);

            return [
                'campaigns' => [],
                'periodDays' => $periodDays,
                'loadError' => $e->getMessage(),
            ];
        }
    }
}
