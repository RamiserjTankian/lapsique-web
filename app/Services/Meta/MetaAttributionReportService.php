<?php

namespace App\Services\Meta;

use App\Models\ContentBooking;
use App\Models\Customer;
use App\Models\MetaCampaignDailyInsight;
use App\Models\TicketOrder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MetaAttributionReportService
{
    public const ORGANIC_KEY = '__organic__';

    public function clearCache(): void
    {
        Cache::forget('meta_attribution_report_keys');

        $keys = Cache::get('meta_attribution_report_keys', []);
        foreach ($keys as $key) {
            Cache::forget($key);
        }

        Cache::forget('meta_attribution_report_keys');
    }

    /**
     * @return array{
     *     since: string,
     *     until: string,
     *     summary: array<string, float|int|null>,
     *     campaigns: array<int, array<string, mixed>>,
     *     daily: array{labels: array<int, string>, spend: array<int, float>, sales: array<int, int>},
     *     last_synced_at: ?string,
     * }
     */
    public function report(Carbon $since, Carbon $until): array
    {
        $since = $since->copy()->startOfDay();
        $until = $until->copy()->endOfDay();

        $cacheKey = sprintf(
            'meta_attribution:%s:%s',
            $since->toDateString(),
            $until->toDateString(),
        );

        $ttl = now()->addMinutes((int) config('meta-ads.report_cache_minutes', 30));

        $this->rememberCacheKey($cacheKey);

        return Cache::remember($cacheKey, $ttl, fn () => $this->buildReport($since, $until));
    }

    protected function rememberCacheKey(string $cacheKey): void
    {
        $keys = Cache::get('meta_attribution_report_keys', []);
        if (! in_array($cacheKey, $keys, true)) {
            $keys[] = $cacheKey;
            Cache::forever('meta_attribution_report_keys', $keys);
        }
    }

    /**
     * @return array{
     *     since: string,
     *     until: string,
     *     summary: array<string, float|int|null>,
     *     campaigns: array<int, array<string, mixed>>,
     *     daily: array{labels: array<int, string>, spend: array<int, float>, sales: array<int, int>},
     *     last_synced_at: ?string,
     * }
     */
    protected function buildReport(Carbon $since, Carbon $until): array
    {
        $spendByCampaign = $this->spendByCampaign($since, $until);
        $leadsByCampaign = $this->mergeCounts([
            $this->popupLeadsByCampaign($since, $until),
            $this->bookingLeadsByCampaign($since, $until),
            $this->ticketLeadsByCampaign($since, $until),
        ]);
        $salesByCampaign = $this->mergeCounts([
            $this->bookingSalesByCampaign($since, $until),
            $this->ticketSalesByCampaign($since, $until),
        ]);
        $revenueByCampaign = $this->mergeAmounts([
            $this->bookingRevenueByCampaign($since, $until),
            $this->ticketRevenueByCampaign($since, $until),
        ]);

        $campaignIds = collect()
            ->merge($spendByCampaign->keys())
            ->merge($leadsByCampaign->keys())
            ->merge($salesByCampaign->keys())
            ->unique()
            ->values();

        $names = $this->hasInsightsTable()
            ? MetaCampaignDailyInsight::query()
                ->whereDate('date', '>=', $since)
                ->whereDate('date', '<=', $until)
                ->whereNotNull('campaign_name')
                ->select('campaign_id', DB::raw('MAX(campaign_name) as campaign_name'))
                ->groupBy('campaign_id')
                ->pluck('campaign_name', 'campaign_id')
            : collect();

        $campaigns = $campaignIds->map(function (string $campaignKey) use (
            $spendByCampaign,
            $leadsByCampaign,
            $salesByCampaign,
            $revenueByCampaign,
            $names,
        ) {
            $spend = (float) ($spendByCampaign[$campaignKey] ?? 0);
            $leads = (int) ($leadsByCampaign[$campaignKey] ?? 0);
            $sales = (int) ($salesByCampaign[$campaignKey] ?? 0);
            $revenue = (float) ($revenueByCampaign[$campaignKey] ?? 0);

            return [
                'campaign_key' => $campaignKey,
                'campaign_id' => $campaignKey === self::ORGANIC_KEY ? null : $campaignKey,
                'campaign_name' => $campaignKey === self::ORGANIC_KEY
                    ? 'Sin campaña / orgánico'
                    : ($names[$campaignKey] ?? "Campaña {$campaignKey}"),
                'spend' => $spend,
                'leads' => $leads,
                'sales_closed' => $sales,
                'revenue' => $revenue,
                'cpl' => $leads > 0 ? round($spend / $leads, 2) : null,
                'cpa' => $sales > 0 ? round($spend / $sales, 2) : null,
                'roas' => $spend > 0 ? round($revenue / $spend, 2) : null,
                'lead_to_sale_rate' => $leads > 0 ? round(($sales / $leads) * 100, 1) : null,
            ];
        })
            ->sortByDesc('spend')
            ->values()
            ->all();

        $summarySpend = (float) $spendByCampaign->sum();
        $summaryLeads = (int) $leadsByCampaign->sum();
        $summarySales = (int) $salesByCampaign->sum();
        $summaryRevenue = (float) $revenueByCampaign->sum();

        return [
            'since' => $since->toDateString(),
            'until' => $until->toDateString(),
            'summary' => [
                'spend' => $summarySpend,
                'leads' => $summaryLeads,
                'sales_closed' => $summarySales,
                'revenue' => $summaryRevenue,
                'cpl' => $summaryLeads > 0 ? round($summarySpend / $summaryLeads, 2) : null,
                'cpa' => $summarySales > 0 ? round($summarySpend / $summarySales, 2) : null,
                'roas' => $summarySpend > 0 ? round($summaryRevenue / $summarySpend, 2) : null,
                'lead_to_sale_rate' => $summaryLeads > 0 ? round(($summarySales / $summaryLeads) * 100, 1) : null,
            ],
            'campaigns' => $campaigns,
            'daily' => $this->dailySeries($since, $until),
            'last_synced_at' => $this->hasInsightsTable()
                ? MetaCampaignDailyInsight::query()->max('synced_at')
                : null,
        ];
    }

    protected function hasInsightsTable(): bool
    {
        return Schema::hasTable('meta_campaign_daily_insights');
    }

    protected function campaignKey(?string $utmCampaign): string
    {
        $value = trim((string) $utmCampaign);

        return $value !== '' ? $value : self::ORGANIC_KEY;
    }

    /**
     * @param  array<int, Collection<string, int|float>>  $collections
     * @return Collection<string, int|float>
     */
    protected function mergeCounts(array $collections): Collection
    {
        $merged = collect();

        foreach ($collections as $collection) {
            foreach ($collection as $key => $value) {
                $merged[$key] = ($merged[$key] ?? 0) + (int) $value;
            }
        }

        return $merged;
    }

    /**
     * @param  array<int, Collection<string, int|float>>  $collections
     * @return Collection<string, float>
     */
    protected function mergeAmounts(array $collections): Collection
    {
        $merged = collect();

        foreach ($collections as $collection) {
            foreach ($collection as $key => $value) {
                $merged[$key] = ($merged[$key] ?? 0) + (float) $value;
            }
        }

        return $merged;
    }

    protected function spendByCampaign(Carbon $since, Carbon $until): Collection
    {
        if (! $this->hasInsightsTable()) {
            return collect();
        }

        return MetaCampaignDailyInsight::query()
            ->whereDate('date', '>=', $since)
            ->whereDate('date', '<=', $until)
            ->select('campaign_id', DB::raw('SUM(spend) as total_spend'))
            ->groupBy('campaign_id')
            ->pluck('total_spend', 'campaign_id')
            ->map(fn ($value) => (float) $value);
    }

    protected function popupLeadsByCampaign(Carbon $since, Carbon $until): Collection
    {
        return Customer::query()
            ->whereBetween('created_at', [$since, $until])
            ->where(function ($query): void {
                $query
                    ->where('source', 'popup')
                    ->orWhereNotNull('metadata->popup_capture');
            })
            ->get(['utm_campaign'])
            ->groupBy(fn (Customer $customer) => $this->campaignKey($customer->utm_campaign))
            ->map->count();
    }

    protected function bookingLeadsByCampaign(Carbon $since, Carbon $until): Collection
    {
        return ContentBooking::query()
            ->whereBetween('created_at', [$since, $until])
            ->get(['utm_campaign'])
            ->groupBy(fn (ContentBooking $booking) => $this->campaignKey($booking->utm_campaign))
            ->map->count();
    }

    protected function ticketLeadsByCampaign(Carbon $since, Carbon $until): Collection
    {
        return TicketOrder::query()
            ->whereBetween('created_at', [$since, $until])
            ->get(['utm_campaign'])
            ->groupBy(fn (TicketOrder $order) => $this->campaignKey($order->utm_campaign))
            ->map->count();
    }

    protected function bookingSalesByCampaign(Carbon $since, Carbon $until): Collection
    {
        if (! Schema::hasTable('content_bookings')) {
            return collect();
        }

        $query = ContentBooking::query()->where('status', 'confirmed');

        if (Schema::hasColumn('content_bookings', 'paid_at')) {
            $query->whereBetween('paid_at', [$since, $until]);
        } else {
            $query->whereBetween('created_at', [$since, $until]);
        }

        return $query
            ->get(['utm_campaign'])
            ->groupBy(fn (ContentBooking $booking) => $this->campaignKey($booking->utm_campaign))
            ->map->count();
    }

    protected function ticketSalesByCampaign(Carbon $since, Carbon $until): Collection
    {
        return TicketOrder::query()
            ->where('status', 'paid')
            ->whereBetween('paid_at', [$since, $until])
            ->get(['utm_campaign'])
            ->groupBy(fn (TicketOrder $order) => $this->campaignKey($order->utm_campaign))
            ->map->count();
    }

    protected function bookingRevenueByCampaign(Carbon $since, Carbon $until): Collection
    {
        if (! Schema::hasTable('content_bookings')) {
            return collect();
        }

        $query = ContentBooking::query()->where('status', 'confirmed');

        if (Schema::hasColumn('content_bookings', 'paid_at')) {
            $query->whereBetween('paid_at', [$since, $until]);
        } else {
            $query->whereBetween('created_at', [$since, $until]);
        }

        return $query
            ->get(['utm_campaign', 'amount'])
            ->groupBy(fn (ContentBooking $booking) => $this->campaignKey($booking->utm_campaign))
            ->map(fn ($group) => (float) $group->sum('amount'));
    }

    protected function ticketRevenueByCampaign(Carbon $since, Carbon $until): Collection
    {
        return TicketOrder::query()
            ->where('status', 'paid')
            ->whereBetween('paid_at', [$since, $until])
            ->get(['utm_campaign', 'total'])
            ->groupBy(fn (TicketOrder $order) => $this->campaignKey($order->utm_campaign))
            ->map(fn ($group) => (float) $group->sum('total'));
    }

    /**
     * @return array{labels: array<int, string>, spend: array<int, float>, sales: array<int, int>}
     */
    protected function dailySeries(Carbon $since, Carbon $until): array
    {
        $labels = [];
        $spend = [];
        $sales = [];

        $spendByDay = $this->hasInsightsTable()
            ? MetaCampaignDailyInsight::query()
                ->whereDate('date', '>=', $since)
                ->whereDate('date', '<=', $until)
                ->select('date', DB::raw('SUM(spend) as total'))
                ->groupBy('date')
                ->pluck('total', 'date')
            : collect();

        $bookingSalesByDay = $this->confirmedBookingsByDay($since, $until);

        $ticketSalesByDay = TicketOrder::query()
            ->where('status', 'paid')
            ->whereBetween('paid_at', [$since, $until])
            ->selectRaw('DATE(paid_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $cursor = $since->copy();
        while ($cursor->lte($until)) {
            $day = $cursor->toDateString();
            $labels[] = $cursor->format('d M');
            $spend[] = round((float) ($spendByDay[$day] ?? 0), 2);
            $sales[] = (int) ($bookingSalesByDay[$day] ?? 0) + (int) ($ticketSalesByDay[$day] ?? 0);
            $cursor->addDay();
        }

        return compact('labels', 'spend', 'sales');
    }

    protected function confirmedBookingsByDay(Carbon $since, Carbon $until): Collection
    {
        if (! Schema::hasTable('content_bookings')) {
            return collect();
        }

        $dateColumn = Schema::hasColumn('content_bookings', 'paid_at') ? 'paid_at' : 'created_at';

        return ContentBooking::query()
            ->where('status', 'confirmed')
            ->whereBetween($dateColumn, [$since, $until])
            ->selectRaw("DATE({$dateColumn}) as day, COUNT(*) as total")
            ->groupBy('day')
            ->pluck('total', 'day');
    }
}
