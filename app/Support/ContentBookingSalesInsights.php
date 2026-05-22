<?php

namespace App\Support;

use App\Models\ContentBooking;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ContentBookingSalesInsights
{
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
}
