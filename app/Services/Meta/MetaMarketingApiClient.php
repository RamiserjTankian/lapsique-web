<?php

namespace App\Services\Meta;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class MetaMarketingApiClient
{
    public function isConfigured(): bool
    {
        return (bool) config('meta-ads.enabled')
            && filled(config('meta-ads.access_token'))
            && filled(config('meta-ads.ad_account_id'));
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function fetchCampaignInsights(Carbon $since, Carbon $until): Collection
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Meta Marketing API is not configured.');
        }

        $accountId = $this->normalizeAdAccountId((string) config('meta-ads.ad_account_id'));
        $version = (string) config('meta-ads.api_version', 'v21.0');
        $url = "https://graph.facebook.com/{$version}/{$accountId}/insights";

        $params = [
            'access_token' => config('meta-ads.access_token'),
            'level' => 'campaign',
            'fields' => implode(',', [
                'campaign_id',
                'campaign_name',
                'spend',
                'impressions',
                'clicks',
                'reach',
                'cpc',
                'cpm',
                'actions',
                'cost_per_action_type',
                'date_start',
                'date_stop',
            ]),
            'time_increment' => 1,
            'time_range' => json_encode([
                'since' => $since->toDateString(),
                'until' => $until->toDateString(),
            ]),
            'limit' => 500,
        ];

        $rows = collect();
        $nextUrl = null;

        do {
            $response = $nextUrl
                ? Http::timeout(60)->get($nextUrl)
                : Http::timeout(60)->get($url, $params);

            if ($response->failed()) {
                $this->throwGraphError($response->json(), $response->status());
            }

            $payload = $response->json();
            $rows = $rows->merge($payload['data'] ?? []);
            $nextUrl = data_get($payload, 'paging.next');
        } while ($nextUrl);

        return $rows;
    }

    protected function normalizeAdAccountId(string $accountId): string
    {
        return str_starts_with($accountId, 'act_') ? $accountId : 'act_'.$accountId;
    }

    /**
     * @param  array<string, mixed>|null  $body
     */
    protected function throwGraphError(?array $body, int $status): void
    {
        $message = (string) data_get($body, 'error.error_user_msg')
            ?: (string) data_get($body, 'error.message', 'Meta Graph API request failed.');

        Log::warning('Meta Marketing API error', [
            'status' => $status,
            'code' => data_get($body, 'error.code'),
            'type' => data_get($body, 'error.type'),
            'message' => $message,
        ]);

        throw new RuntimeException($message, $status);
    }
}
