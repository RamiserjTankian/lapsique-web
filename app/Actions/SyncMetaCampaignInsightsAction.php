<?php

namespace App\Actions;

use App\Models\MetaCampaignDailyInsight;
use App\Services\Meta\MetaMarketingApiClient;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class SyncMetaCampaignInsightsAction
{
    public function __construct(
        protected MetaMarketingApiClient $client,
    ) {}

    /**
     * @return array{synced: int, skipped: int}
     */
    public function execute(int $days): array
    {
        if (! $this->client->isConfigured()) {
            throw new \RuntimeException('Meta Marketing API is not configured.');
        }

        $until = now()->startOfDay();
        $since = now()->subDays(max($days, 1))->startOfDay();
        $rows = $this->client->fetchCampaignInsights($since, $until);

        $synced = 0;
        $skipped = 0;
        $syncedAt = now();

        foreach ($rows as $row) {
            $campaignId = (string) ($row['campaign_id'] ?? '');
            $date = (string) ($row['date_start'] ?? $row['date_stop'] ?? '');

            if ($campaignId === '' || $date === '') {
                $skipped++;

                continue;
            }

            MetaCampaignDailyInsight::query()->updateOrCreate(
                [
                    'date' => Carbon::parse($date)->toDateString(),
                    'campaign_id' => $campaignId,
                ],
                [
                    'campaign_name' => $row['campaign_name'] ?? null,
                    'spend' => (float) ($row['spend'] ?? 0),
                    'impressions' => (int) ($row['impressions'] ?? 0),
                    'clicks' => (int) ($row['clicks'] ?? 0),
                    'reach' => (int) ($row['reach'] ?? 0),
                    'cpc' => isset($row['cpc']) ? (float) $row['cpc'] : null,
                    'cpm' => isset($row['cpm']) ? (float) $row['cpm'] : null,
                    'actions' => $row['actions'] ?? null,
                    'cost_per_action_type' => $row['cost_per_action_type'] ?? null,
                    'synced_at' => $syncedAt,
                ],
            );

            $synced++;
        }

        Log::info('Meta campaign insights synced', [
            'since' => $since->toDateString(),
            'until' => $until->toDateString(),
            'synced' => $synced,
            'skipped' => $skipped,
        ]);

        return ['synced' => $synced, 'skipped' => $skipped];
    }
}
