<?php

namespace App\Support;

use App\Models\ContentBooking;
use Illuminate\Support\Carbon;

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

    /**
     * @return array{revenue: float, orders: int, average: float, pending: int, failed: int}
     */
    public static function periodStats(): array
    {
        $since = self::since();

        $confirmed = ContentBooking::query()
            ->where('status', 'confirmed')
            ->where('paid_at', '>=', $since);

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
     * @return array<string, int>
     */
    public static function revenueByProvider(): array
    {
        return ContentBooking::query()
            ->where('status', 'confirmed')
            ->where('paid_at', '>=', self::since())
            ->selectRaw('coalesce(payment_provider, \'unknown\') as provider, count(*) as total')
            ->groupBy('provider')
            ->pluck('total', 'provider')
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

        $counts = ContentBooking::query()
            ->where('status', 'confirmed')
            ->where('paid_at', '>=', $since)
            ->selectRaw('date(paid_at) as day, count(*) as total')
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
        $confirmed = ContentBooking::query()
            ->where('status', 'confirmed')
            ->where('paid_at', '>=', $since)
            ->count();

        if ($started === 0) {
            return null;
        }

        return round(($confirmed / $started) * 100, 1);
    }
}
