<?php

namespace App\Support;

use App\Models\AnalyticsEvent;
use App\Models\AnalyticsPageview;
use App\Models\ContentBooking;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ContentBookingSalesInsights
{
    /**
     * @return array<string, array{label: string, paths: array<int, string>, service_types: array<int, string>, page_event: string, lead_events: array<int, string>, contact_events: array<int, string>}>
     */
    public static function menuServices(): array
    {
        return [
            'food_reels' => [
                'label' => 'Comida',
                'paths' => ['/reels-de-comida', '/comida-y-reels'],
                'service_types' => ['food_reels'],
                'page_event' => 'food_reels_page_viewed',
                'lead_events' => ['food_reels_booking_cta_clicked'],
                'contact_events' => ['food_reels_whatsapp_cta_clicked', 'whatsapp_popup_clicked'],
            ],
            ContentBooking::SERVICE_DJ_SET => [
                'label' => 'DJ Sets',
                'paths' => ['/dj-set', '/djset'],
                'service_types' => [ContentBooking::SERVICE_DJ_SET],
                'page_event' => 'djset_page_viewed',
                'lead_events' => ['djset_booking_cta_clicked'],
                'contact_events' => ['djset_whatsapp_cta_clicked', 'whatsapp_popup_clicked'],
            ],
            ContentBooking::SERVICE_DRONE_SESSION => [
                'label' => 'Vuelos con dron',
                'paths' => ['/sesiones-de-dron', '/drone-session', '/vuelos-con-dron'],
                'service_types' => [ContentBooking::SERVICE_DRONE_SESSION],
                'page_event' => 'drone_session_page_viewed',
                'lead_events' => ['drone_session_booking_cta_clicked'],
                'contact_events' => ['drone_session_whatsapp_cta_clicked', 'whatsapp_popup_clicked'],
            ],
            ContentBooking::SERVICE_CONSTRUCTION_PROGRESS => [
                'label' => 'Avances de obra',
                'paths' => ['/avances-de-obra'],
                'service_types' => [ContentBooking::SERVICE_CONSTRUCTION_PROGRESS],
                'page_event' => 'construction_progress_page_viewed',
                'lead_events' => ['construction_progress_booking_cta_clicked'],
                'contact_events' => ['construction_progress_whatsapp_cta_clicked', 'whatsapp_popup_clicked'],
            ],
            ContentBooking::SERVICE_MULTI_CAMERA => [
                'label' => 'Multicámara',
                'paths' => ['/multicamara'],
                'service_types' => [ContentBooking::SERVICE_MULTI_CAMERA],
                'page_event' => 'multi_camera_page_viewed',
                'lead_events' => ['multi_camera_booking_cta_clicked'],
                'contact_events' => ['multi_camera_whatsapp_cta_clicked', 'whatsapp_popup_clicked'],
            ],
        ];
    }

    public static function periodDays(): int
    {
        return (int) config('analytics.dashboard_days', 30);
    }

    public static function since(): Carbon
    {
        return now()->subDays(self::periodDays());
    }

    public static function saleAtExpression(): string
    {
        return 'coalesce(paid_at, created_at)';
    }

    public static function confirmedSalesQuery(): Builder
    {
        return ContentBooking::query()
            ->where('status', 'confirmed')
            ->whereRaw(self::saleAtExpression().' >= ?', [self::since()]);
    }

    /**
     * @return array{revenue: float, orders: int, average: float, pending: int, failed: int}
     */
    public static function periodStats(): array
    {
        $since = self::since();
        $confirmed = self::confirmedSalesQuery();

        $revenue = (float) (clone $confirmed)->sum('amount');
        $orders = (clone $confirmed)->count();

        return [
            'revenue' => $revenue,
            'orders' => $orders,
            'average' => $orders > 0 ? $revenue / $orders : 0,
            'pending' => ContentBooking::query()
                ->whereIn('status', ['pending', 'pending_payment'])
                ->where('created_at', '>=', $since)
                ->count(),
            'failed' => ContentBooking::query()
                ->where('status', 'failed')
                ->where('created_at', '>=', $since)
                ->count(),
        ];
    }

    /**
     * @return array<string, float>
     */
    public static function revenueByProvider(): array
    {
        return self::confirmedSalesQuery()
            ->selectRaw("coalesce(payment_provider, 'unknown') as provider, sum(amount) as total")
            ->groupBy('provider')
            ->pluck('total', 'provider')
            ->map(fn ($total) => (float) $total)
            ->all();
    }

    /**
     * @return array<string, float>
     */
    public static function revenueByService(): array
    {
        return self::confirmedSalesQuery()
            ->selectRaw('service_type, sum(amount) as total')
            ->groupBy('service_type')
            ->pluck('total', 'service_type')
            ->map(fn ($total) => (float) $total)
            ->all();
    }

    /**
     * @return array{labels: array<int, string>, data: array<int, int>}
     */
    public static function dailyConfirmedChart(): array
    {
        $since = self::since()->startOfDay();
        $labels = [];
        $data = [];
        $saleAt = self::saleAtExpression();

        $driver = DB::connection()->getDriverName();
        $dayExpression = $driver === 'sqlite'
            ? "date({$saleAt})"
            : "date({$saleAt})";

        $counts = ContentBooking::query()
            ->where('status', 'confirmed')
            ->whereRaw("{$saleAt} >= ?", [$since])
            ->selectRaw("{$dayExpression} as day, count(*) as total")
            ->groupBy('day')
            ->pluck('total', 'day');

        for ($i = 0; $i <= self::periodDays(); $i++) {
            $day = $since->copy()->addDays($i)->toDateString();
            $labels[] = Carbon::parse($day)->format('d M');
            $data[] = (int) ($counts[$day] ?? 0);
        }

        return ['labels' => $labels, 'data' => $data];
    }

    public static function checkoutConversionRate(): ?float
    {
        $since = self::since();
        $started = ContentBooking::query()->where('created_at', '>=', $since)->count();
        $confirmed = (clone self::confirmedSalesQuery())->count();

        if ($started === 0) {
            return null;
        }

        return round(($confirmed / $started) * 100, 1);
    }

    /**
     * @return array<int, array{key: string, label: string, pageviews: int, leads: int, whatsapp: int, bookings: int, confirmed: int, pending: int, revenue: float, conversion_rate: float|null}>
     */
    public static function menuServiceRows(): array
    {
        $since = self::since();

        return collect(self::menuServices())
            ->map(function (array $service, string $key) use ($since): array {
                $bookings = self::bookingsForMenuService($key)
                    ->where('created_at', '>=', $since);

                $bookingCount = (clone $bookings)->count();
                $confirmed = (clone $bookings)->where('status', 'confirmed')->count();
                $pending = (clone $bookings)->whereIn('status', ['pending', 'pending_payment'])->count();
                $revenue = (float) (clone $bookings)->where('status', 'confirmed')->sum('amount');
                $pageviews = self::pageviewsForMenuService($key, $service, $since);
                $leads = self::eventsForMenuService($key, $service, $service['lead_events'], $since)->count();
                $whatsapp = self::eventsForMenuService($key, $service, $service['contact_events'], $since)->count();

                return [
                    'key' => $key,
                    'label' => $service['label'],
                    'pageviews' => $pageviews,
                    'leads' => $leads,
                    'whatsapp' => $whatsapp,
                    'bookings' => $bookingCount,
                    'confirmed' => $confirmed,
                    'pending' => $pending,
                    'revenue' => $revenue,
                    'conversion_rate' => $leads > 0 ? round(($bookingCount / $leads) * 100, 1) : null,
                ];
            })
            ->values()
            ->all();
    }

    public static function bookingsForMenuService(string $key): Builder
    {
        return self::constrainBookingsToMenuService(ContentBooking::query(), $key);
    }

    public static function constrainBookingsToMenuService(Builder $query, string $key): Builder
    {
        $service = self::menuServices()[$key] ?? null;

        if (! $service) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $query) use ($key, $service): void {
            if ($key !== 'food_reels') {
                $query->whereIn('service_type', $service['service_types']);
            }

            foreach ($service['paths'] as $path) {
                $query->orWhere('landing_url', 'like', '%'.$path.'%')
                    ->orWhere('referrer', 'like', '%'.$path.'%');
            }
        });
    }

    public static function menuServiceLabelForBooking(ContentBooking $booking): string
    {
        $landingUrl = (string) ($booking->landing_url ?: $booking->referrer);

        foreach (self::menuServices() as $key => $service) {
            if ($key !== 'food_reels' && $booking->service_type === $key) {
                return $service['label'];
            }

            foreach ($service['paths'] as $path) {
                if ($landingUrl !== '' && str_contains($landingUrl, $path)) {
                    return $service['label'];
                }
            }
        }

        return $booking->service_short_name;
    }

    protected static function pageviewsForMenuService(string $key, array $service, Carbon $since): int
    {
        $pageviews = AnalyticsPageview::query()
            ->where('created_at', '>=', $since)
            ->where(fn (Builder $query) => self::wherePathIn($query, $service['paths']))
            ->count();

        if ($pageviews > 0) {
            return $pageviews;
        }

        return self::eventsForMenuService($key, $service, [$service['page_event']], $since)->count();
    }

    /**
     * @param  array<int, string>  $eventNames
     */
    protected static function eventsForMenuService(string $key, array $service, array $eventNames, Carbon $since): Builder
    {
        return AnalyticsEvent::query()
            ->where('created_at', '>=', $since)
            ->whereIn('name', $eventNames)
            ->where(function (Builder $query) use ($key, $service): void {
                $query->where(fn (Builder $pathQuery) => self::wherePathIn($pathQuery, $service['paths']));

                foreach ($service['service_types'] as $serviceType) {
                    $query->orWhere('metadata->service_type', $serviceType);
                }

                if ($key !== 'food_reels') {
                    $query->orWhere('metadata->content_category', $key.'_booking');
                } else {
                    $query->orWhere('metadata->content_category', 'food_reels_booking');
                }
            });
    }

    /**
     * @param  array<int, string>  $paths
     */
    protected static function wherePathIn(Builder $query, array $paths): void
    {
        foreach ($paths as $path) {
            $query->orWhere('path', $path)
                ->orWhere('url', 'like', '%'.$path.'%');
        }
    }
}
