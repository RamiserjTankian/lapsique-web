<?php

namespace App\Services;

use App\Models\AnalyticsEvent;
use App\Models\AnalyticsPageview;
use App\Models\AnalyticsSession;
use App\Models\GuestListEntry;
use App\Models\TicketOrder;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class AnalyticsInsightsService
{
    public function __construct(
        protected AnalyticsSessionEnrichmentService $sessionEnrichmentService
    ) {
    }

    public function dashboard(?int $days = null): array
    {
        $days ??= (int) config('analytics.dashboard_days', 30);
        $timezone = $this->reportingTimezone();
        $end = now();
        $start = now($timezone)->subDays(max($days - 1, 0))->startOfDay()->utc();

        return Cache::remember(
            "analytics:dashboard:{$days}",
            now()->addMinutes(5),
            fn (): array => $this->buildSnapshot($start, $end, $timezone, 'Ultimos ' . $days . ' dias')
        );
    }

    public function forToday(): array
    {
        $timezone = $this->reportingTimezone();
        $start = now($timezone)->startOfDay()->utc();
        $end = now();

        return Cache::remember(
            'analytics:summary:today',
            now()->addMinutes(2),
            fn (): array => $this->buildSnapshot($start, $end, $timezone, 'Hoy')
        );
    }

    public function forDate(CarbonInterface $date): array
    {
        $timezone = $this->reportingTimezone();
        $start = Carbon::parse($date, $timezone)->startOfDay()->utc();
        $end = Carbon::parse($date, $timezone)->endOfDay()->utc();
        $cacheKey = 'analytics:summary:' . Carbon::parse($date, $timezone)->format('Y-m-d');

        return Cache::remember(
            $cacheKey,
            now()->addMinutes(15),
            fn (): array => $this->buildSnapshot($start, $end, $timezone, Carbon::parse($date, $timezone)->translatedFormat('d M Y'))
        );
    }

    public function clearCachedSnapshots(): void
    {
        Cache::forget('analytics:summary:today');

        for ($days = 1; $days <= 90; $days++) {
            Cache::forget("analytics:dashboard:{$days}");
        }
    }

    public function orderAttribution(TicketOrder $order): array
    {
        $sessionId = data_get($order->metadata, 'analytics_session_id');
        $session = $sessionId
            ? AnalyticsSession::query()->where('session_id', $sessionId)->first()
            : null;
        $derivedSource = $session ? $this->derivedSource($session) : null;

        $source = $session
            ? $this->normalizeSourceLabel($derivedSource['source_label'] ?? null, $derivedSource['source_type'] ?? null)
            : ($order->utm_source ?: 'direct');

        return [
            'session' => $session,
            'source_type' => $derivedSource['source_type'] ?? ($order->utm_source ? 'campaign' : 'direct'),
            'source_label' => $source,
            'location' => $this->formatLocation($session),
            'landing_path' => $session?->landing_path,
            'ip_address' => $session?->ip_address,
        ];
    }

    protected function buildSnapshot(CarbonInterface $start, CarbonInterface $end, string $timezone, string $label): array
    {
        $sessions = AnalyticsSession::query()
            ->whereBetween('created_at', [$start, $end])
            ->withCount(['pageviews', 'events'])
            ->get();

        $pageviews = AnalyticsPageview::query()
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $events = AnalyticsEvent::query()
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $paidOrders = TicketOrder::query()
            ->where('status', 'paid')
            ->whereBetween('paid_at', [$start, $end])
            ->get();

        $guestListEntries = GuestListEntry::query()
            ->whereBetween('created_at', [$start, $end])
            ->count();

        $repeatVisitors = $sessions
            ->groupBy('visitor_id')
            ->filter(fn (Collection $items) => $items->count() > 1)
            ->count();

        $repeatPageviews = $pageviews
            ->groupBy('visitor_id')
            ->filter(fn (Collection $items) => $items->count() > 1)
            ->sum(fn (Collection $items) => $items->count());

        $paidOrdersAttributed = $paidOrders->filter(
            fn (TicketOrder $order) => filled(data_get($order->metadata, 'analytics_session_id'))
        );

        return [
            'label' => $label,
            'timezone' => $timezone,
            'start' => Carbon::parse($start)->timezone($timezone),
            'end' => Carbon::parse($end)->timezone($timezone),
            'stats' => [
                'sessions' => $sessions->count(),
                'unique_visitors' => $sessions->pluck('visitor_id')->filter()->unique()->count(),
                'repeat_visitors' => $repeatVisitors,
                'new_visitors' => max($sessions->pluck('visitor_id')->filter()->unique()->count() - $repeatVisitors, 0),
                'pageviews' => $pageviews->count(),
                'repeat_pageviews' => $repeatPageviews,
                'events' => $events->count(),
                'paid_orders' => $paidOrders->count(),
                'paid_orders_attributed' => $paidOrdersAttributed->count(),
                'revenue' => (float) $paidOrders->sum('total'),
                'guestlist_registrations' => $guestListEntries,
                'pages_per_session' => $sessions->count() > 0 ? round($pageviews->count() / $sessions->count(), 2) : 0,
                'events_per_session' => $sessions->count() > 0 ? round($events->count() / $sessions->count(), 2) : 0,
                'sales_conversion_rate' => $sessions->count() > 0
                    ? round(($paidOrders->count() / $sessions->count()) * 100, 2)
                    : 0,
            ],
            'daily_traffic' => $this->dailyTraffic($sessions, $pageviews, $paidOrders, $timezone),
            'hourly_activity' => $this->hourlyActivity($sessions, $pageviews, $timezone),
            'top_pages' => $this->topPages($pageviews),
            'source_breakdown' => $this->sourceBreakdown($sessions),
            'top_locations' => $this->topLocations($sessions),
            'top_events' => $this->topEvents($events),
            'peak_hours' => $this->peakHours($pageviews, $timezone),
            'recent_sessions' => $sessions
                ->sortByDesc('created_at')
                ->take(12)
                ->values(),
            'paid_orders' => $paidOrders,
        ];
    }

    protected function dailyTraffic(Collection $sessions, Collection $pageviews, Collection $paidOrders, string $timezone): array
    {
        $sessionCounts = $sessions
            ->groupBy(fn (AnalyticsSession $session) => $session->created_at?->timezone($timezone)->format('Y-m-d'))
            ->map->count();

        $uniqueVisitors = $sessions
            ->groupBy(fn (AnalyticsSession $session) => $session->created_at?->timezone($timezone)->format('Y-m-d'))
            ->map(fn (Collection $items) => $items->pluck('visitor_id')->filter()->unique()->count());

        $pageviewCounts = $pageviews
            ->groupBy(fn (AnalyticsPageview $pageview) => $pageview->created_at?->timezone($timezone)->format('Y-m-d'))
            ->map->count();

        $orderCounts = $paidOrders
            ->groupBy(fn (TicketOrder $order) => $order->paid_at?->timezone($timezone)->format('Y-m-d'))
            ->map->count();

        return collect($sessionCounts->keys())
            ->merge($pageviewCounts->keys())
            ->merge($orderCounts->keys())
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->map(function (string $date) use ($sessionCounts, $uniqueVisitors, $pageviewCounts, $orderCounts, $timezone) {
                $carbon = Carbon::createFromFormat('Y-m-d', $date, $timezone);

                return [
                    'date' => $date,
                    'label' => $carbon->translatedFormat('d M'),
                    'sessions' => (int) ($sessionCounts[$date] ?? 0),
                    'unique_visitors' => (int) ($uniqueVisitors[$date] ?? 0),
                    'pageviews' => (int) ($pageviewCounts[$date] ?? 0),
                    'paid_orders' => (int) ($orderCounts[$date] ?? 0),
                ];
            })
            ->all();
    }

    protected function hourlyActivity(Collection $sessions, Collection $pageviews, string $timezone): array
    {
        $sessionCounts = $sessions
            ->groupBy(fn (AnalyticsSession $session) => $session->created_at?->timezone($timezone)->format('H'))
            ->map->count();

        $pageviewCounts = $pageviews
            ->groupBy(fn (AnalyticsPageview $pageview) => $pageview->created_at?->timezone($timezone)->format('H'))
            ->map->count();

        return collect(range(0, 23))
            ->map(function (int $hour) use ($sessionCounts, $pageviewCounts) {
                $key = str_pad((string) $hour, 2, '0', STR_PAD_LEFT);

                return [
                    'hour' => $hour,
                    'label' => "{$key}:00",
                    'sessions' => (int) ($sessionCounts[$key] ?? 0),
                    'pageviews' => (int) ($pageviewCounts[$key] ?? 0),
                ];
            })
            ->all();
    }

    protected function topPages(Collection $pageviews): array
    {
        return $pageviews
            ->groupBy('path')
            ->map(function (Collection $items, string $path) {
                return [
                    'path' => $path ?: '/',
                    'pageviews' => $items->count(),
                    'unique_visitors' => $items->pluck('visitor_id')->filter()->unique()->count(),
                ];
            })
            ->sortByDesc('pageviews')
            ->take(8)
            ->values()
            ->all();
    }

    protected function sourceBreakdown(Collection $sessions): array
    {
        return $sessions
            ->groupBy(fn (AnalyticsSession $session) => $this->derivedSource($session)['source_type'])
            ->map(function (Collection $items, string $type) {
                return [
                    'type' => $type,
                    'label' => $this->sourceTypeLabel($type),
                    'sessions' => $items->count(),
                    'unique_visitors' => $items->pluck('visitor_id')->filter()->unique()->count(),
                    'top_source' => $this->normalizeSourceLabel(
                        $items->countBy(fn (AnalyticsSession $session) => $this->derivedSource($session)['source_label'])
                            ->sortDesc()
                            ->keys()
                            ->first(),
                        $type
                    ),
                ];
            })
            ->sortByDesc('sessions')
            ->values()
            ->all();
    }

    protected function topLocations(Collection $sessions): array
    {
        return $sessions
            ->groupBy(function (AnalyticsSession $session) {
                $parts = $this->locationParts($session);

                return implode('|', [
                    $parts['country_name'],
                    $parts['region'],
                    $parts['city'],
                ]);
            })
            ->map(function (Collection $items, string $key) {
                [$country, $region, $city] = explode('|', $key);

                return [
                    'country_name' => $country,
                    'region' => $region,
                    'city' => $city,
                    'sessions' => $items->count(),
                    'unique_visitors' => $items->pluck('visitor_id')->filter()->unique()->count(),
                ];
            })
            ->sortByDesc('sessions')
            ->take(10)
            ->values()
            ->all();
    }

    protected function topEvents(Collection $events): array
    {
        return $events
            ->groupBy(fn (AnalyticsEvent $event) => ($event->name ?: 'event') . '|' . ($event->category ?: 'general'))
            ->map(function (Collection $items, string $key) {
                [$name, $category] = explode('|', $key);

                return [
                    'name' => $name,
                    'category' => $category,
                    'count' => $items->count(),
                    'unique_visitors' => $items->pluck('visitor_id')->filter()->unique()->count(),
                    'last_triggered_at' => $items->max('created_at'),
                ];
            })
            ->sortByDesc('count')
            ->take(10)
            ->values()
            ->all();
    }

    protected function peakHours(Collection $pageviews, string $timezone): array
    {
        return collect($this->hourlyActivity(collect(), $pageviews, $timezone))
            ->sortByDesc('pageviews')
            ->take(3)
            ->values()
            ->all();
    }

    protected function formatLocation(?AnalyticsSession $session): string
    {
        if (! $session) {
            return 'Sin atribucion';
        }

        $parts = array_filter(array_values($this->locationParts($session)));

        return $parts !== [] ? implode(', ', $parts) : 'Ubicacion desconocida';
    }

    protected function normalizeSourceLabel(?string $label, ?string $type = null): string
    {
        $label = trim((string) $label);

        if ($label !== '') {
            return $label;
        }

        return $this->sourceTypeLabel($type ?: 'direct');
    }

    public function sourceTypeLabel(string $type): string
    {
        return match ($type) {
            'social' => 'Social',
            'search' => 'Buscador',
            'email' => 'Email',
            'paid' => 'Pago',
            'internal' => 'Interno',
            'referral' => 'Referencia',
            default => 'Directo',
        };
    }

    protected function reportingTimezone(): string
    {
        return (string) config('analytics.reporting_timezone', 'America/Merida');
    }

    protected function locationParts(AnalyticsSession $session): array
    {
        return [
            'country_name' => $this->normalizeCountryName($session->country, $session->country_name),
            'region' => $this->normalizePlaceName($session->region ?: $session->region_code, 'Sin estado'),
            'city' => $this->normalizePlaceName($session->city, 'Sin ciudad'),
        ];
    }

    protected function derivedSource(AnalyticsSession $session): array
    {
        if ($session->source_type && $session->source_label) {
            return [
                'source_type' => $session->source_type,
                'source_label' => $session->source_label,
            ];
        }

        return $this->sessionEnrichmentService->resolveSource(
            $session->utm_source,
            $session->utm_medium,
            $session->referrer_domain,
            $session->landing_url
        );
    }

    protected function normalizeCountryName(?string $countryCode, ?string $countryName): string
    {
        if ($countryCode && class_exists(\Locale::class)) {
            $resolved = \Locale::getDisplayRegion('-' . strtoupper($countryCode), 'es');

            if (is_string($resolved) && trim($resolved) !== '') {
                return trim($resolved);
            }
        }

        return $this->normalizePlaceName($countryName, 'Desconocido');
    }

    protected function normalizePlaceName(?string $value, string $fallback): string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return $fallback;
        }

        return Str::title(Str::ascii($value));
    }
}
