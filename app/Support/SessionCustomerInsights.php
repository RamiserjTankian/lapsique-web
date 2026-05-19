<?php

namespace App\Support;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Builder;

class SessionCustomerInsights
{
    public static function applyBookingStats(Builder $query): Builder
    {
        return $query
            ->withCount([
                'contentBookings',
                'contentBookings as confirmed_bookings_count' => fn (Builder $q) => $q->where('status', 'confirmed'),
                'contentBookings as pending_delivery_count' => fn (Builder $q) => $q
                    ->where('status', 'confirmed')
                    ->whereNull('deliverables_ready_at'),
            ])
            ->withSum(
                ['contentBookings as total_revenue' => fn (Builder $q) => $q->where('status', 'confirmed')],
                'amount',
            )
            ->withMax(
                ['contentBookings as last_booking_at' => fn (Builder $q) => $q->where('status', 'confirmed')],
                'paid_at',
            );
    }

    /**
     * @return array{bookings: int, confirmed: int, revenue: float, pending_delivery: int, last_paid_at: ?string}
     */
    public static function profileStats(Customer $customer): array
    {
        $bookings = $customer->contentBookings();

        return [
            'bookings' => (clone $bookings)->count(),
            'confirmed' => (clone $bookings)->where('status', 'confirmed')->count(),
            'revenue' => (float) (clone $bookings)->where('status', 'confirmed')->sum('amount'),
            'pending_delivery' => (clone $bookings)
                ->where('status', 'confirmed')
                ->whereNull('deliverables_ready_at')
                ->count(),
            'last_paid_at' => (clone $bookings)->where('status', 'confirmed')->max('paid_at'),
        ];
    }
}
