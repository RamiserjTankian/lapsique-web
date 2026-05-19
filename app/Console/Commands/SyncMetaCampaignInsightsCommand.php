<?php

namespace App\Console\Commands;

use App\Actions\SyncMetaCampaignInsightsAction;
use App\Services\Meta\MetaAttributionReportService;
use App\Services\Meta\MetaMarketingApiClient;
use Illuminate\Console\Command;

class SyncMetaCampaignInsightsCommand extends Command
{
    protected $signature = 'meta:sync-campaign-insights {--days= : Number of days to sync (default from config)}';

    protected $description = 'Sync Meta Ads campaign spend and performance insights into the database';

    public function handle(
        SyncMetaCampaignInsightsAction $sync,
        MetaMarketingApiClient $client,
        MetaAttributionReportService $reports,
    ): int {
        if (! $client->isConfigured()) {
            $this->error('Meta Marketing API is not configured. Set META_ADS_ENABLED=true and provide META_ACCESS_TOKEN and META_AD_ACCOUNT_ID.');

            return self::FAILURE;
        }

        $days = (int) ($this->option('days') ?: config('meta-ads.sync_days_default', 30));

        try {
            $result = $sync->execute($days);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            report($e);

            return self::FAILURE;
        }

        $reports->clearCache();

        $this->info(sprintf(
            'Meta insights synced for the last %d days. Rows upserted: %d | Skipped: %d',
            $days,
            $result['synced'],
            $result['skipped'],
        ));

        return self::SUCCESS;
    }
}
