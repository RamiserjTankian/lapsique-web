<?php

namespace App\Filament\Widgets\Concerns;

trait InteractsWithMetaAdsPeriod
{
    public int $periodDays = 30;

    protected function getMetaAdsPeriodDays(): int
    {
        $period = $this->periodDays;

        if (! in_array($period, [7, 30, 90], true)) {
            $period = (int) session(
                'meta_ads_period_days',
                request()->query('period', config('meta.attribution.sync_days_default', 30)),
            );
        }

        return in_array($period, [7, 30, 90], true)
            ? $period
            : (int) config('meta.attribution.sync_days_default', 30);
    }
}
