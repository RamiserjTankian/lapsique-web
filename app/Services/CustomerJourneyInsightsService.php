<?php

namespace App\Services;

use App\Models\AnalyticsEvent;
use App\Models\AnalyticsSession;
use App\Models\ContentBooking;
use App\Models\Customer;
use App\Models\GuestListEntry;
use App\Models\PosCharge;
use App\Models\TicketOrder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class CustomerJourneyInsightsService
{
    public function dashboard(?int $days = null): array
    {
        $days ??= (int) config('analytics.dashboard_days', 30);

        return Cache::remember(
            "customer-journey:dashboard:{$days}",
            now()->addMinutes(5),
            fn (): array => $this->buildSnapshot($days),
        );
    }

    public function clearCache(): void
    {
        foreach ([7, 30, 60, 90, (int) config('analytics.dashboard_days', 30)] as $days) {
            Cache::forget("customer-journey:dashboard:{$days}");
        }
    }

    protected function buildSnapshot(int $days): array
    {
        $since = now()->subDays($days);

        $sessions = AnalyticsSession::query()
            ->where('created_at', '>=', $since)
            ->withCount(['pageviews', 'events'])
            ->get();

        $events = AnalyticsEvent::query()
            ->where('created_at', '>=', $since)
            ->get();

        $customers = Customer::query()
            ->withCount([
                'analyticsSessions',
                'analyticsPageviews',
                'analyticsEvents',
                'guestListEntries',
                'ticketOrders',
                'contentBookings',
                'ticketOrders as paid_ticket_orders_count' => fn ($query) => $query->where('status', 'paid'),
                'contentBookings as confirmed_content_bookings_count' => fn ($query) => $query->where('status', 'confirmed'),
            ])
            ->withSum(['ticketOrders as paid_ticket_revenue' => fn ($query) => $query->where('status', 'paid')], 'total')
            ->withSum(['contentBookings as confirmed_booking_revenue' => fn ($query) => $query->where('status', 'confirmed')], 'amount')
            ->where(function ($query) use ($since): void {
                $query
                    ->where('created_at', '>=', $since)
                    ->orWhere('last_interaction_at', '>=', $since)
                    ->orWhereHas('analyticsSessions', fn ($sessions) => $sessions->where('created_at', '>=', $since))
                    ->orWhereHas('ticketOrders', fn ($orders) => $orders->where('created_at', '>=', $since))
                    ->orWhereHas('contentBookings', fn ($bookings) => $bookings->where('created_at', '>=', $since));
            })
            ->get();

        $ticketOrders = TicketOrder::query()
            ->where('created_at', '>=', $since)
            ->get();

        $contentBookings = ContentBooking::query()
            ->where('created_at', '>=', $since)
            ->get();

        $paidTicketOrders = $ticketOrders->where('status', 'paid');
        $confirmedBookings = $contentBookings->where('status', 'confirmed');
        $posConsumed = (float) PosCharge::query()
            ->where('created_at', '>=', $since)
            ->sum('total');

        $checkoutCustomers = $ticketOrders->pluck('customer_id')
            ->merge($contentBookings->pluck('customer_id'))
            ->filter()
            ->unique()
            ->count();

        $paidCustomerIds = $paidTicketOrders->pluck('customer_id')
            ->merge($confirmedBookings->pluck('customer_id'))
            ->filter()
            ->unique();

        $engagedSessionIds = $events
            ->filter(fn (AnalyticsEvent $event): bool => in_array($event->name, [
                'engaged',
                'scroll_depth',
                'section_view',
                'click',
                'booking_widget_viewed',
                'reel_player_opened',
                'tickets_added_to_cart',
                'checkout_started',
                'booking_checkout_started',
            ], true))
            ->pluck('analytics_session_id')
            ->merge($sessions->filter(fn (AnalyticsSession $session): bool => (int) $session->pageviews_count > 1)->pluck('id'))
            ->unique();

        return [
            'days' => $days,
            'stats' => [
                'visitors' => $sessions->pluck('visitor_id')->filter()->unique()->count(),
                'sessions' => $sessions->count(),
                'engaged_sessions' => $engagedSessionIds->count(),
                'identified_leads' => $customers->count(),
                'checkout_customers' => $checkoutCustomers,
                'pending_payments' => $ticketOrders->where('status', 'pending')->count()
                    + $contentBookings->whereIn('status', ['pending', 'pending_payment'])->count(),
                'paid_customers' => $paidCustomerIds->count(),
                'repeat_customers' => $this->repeatCustomers($customers),
                'ticket_revenue' => (float) $paidTicketOrders->sum('total'),
                'booking_revenue' => (float) $confirmedBookings->sum('amount'),
                'pos_consumed' => $posConsumed,
                'guestlist_registrations' => GuestListEntry::query()->where('created_at', '>=', $since)->count(),
                'failed_payments' => $ticketOrders->whereIn('status', ['failed', 'cancelled'])->count()
                    + $contentBookings->whereIn('status', ['failed', 'cancelled'])->count(),
            ],
            'funnel' => $this->funnel($sessions, $engagedSessionIds, $customers, $checkoutCustomers, $paidCustomerIds),
            'sources' => $this->sourceRows($sessions, $since),
            'dropoffs' => $this->dropoffs($events),
        ];
    }

    protected function funnel(Collection $sessions, Collection $engagedSessionIds, Collection $customers, int $checkoutCustomers, Collection $paidCustomerIds): array
    {
        $visitors = $sessions->pluck('visitor_id')->filter()->unique()->count();
        $rows = [
            ['stage' => 'Visitantes', 'count' => $visitors],
            ['stage' => 'Engaged', 'count' => $engagedSessionIds->count()],
            ['stage' => 'Leads identificados', 'count' => $customers->count()],
            ['stage' => 'Checkout / registro', 'count' => $checkoutCustomers],
            ['stage' => 'Clientes que pagan', 'count' => $paidCustomerIds->count()],
        ];
        $previous = null;

        return collect($rows)
            ->map(function (array $row) use (&$previous, $visitors): array {
                $count = (int) $row['count'];
                $row['conversion_rate'] = $previous > 0 ? round(($count / $previous) * 100, 1) : null;
                $row['visitor_rate'] = $visitors > 0 ? round(($count / $visitors) * 100, 1) : 0.0;
                $previous = $count;

                return $row;
            })
            ->all();
    }

    protected function sourceRows(Collection $sessions, Carbon $since): array
    {
        $primarySourceByCustomer = $sessions
            ->whereNotNull('customer_id')
            ->sortBy('created_at')
            ->groupBy('customer_id')
            ->map(fn (Collection $items): string => $items->first()?->source_label
                ?: $items->first()?->utm_source
                ?: $items->first()?->referrer_domain
                ?: 'direct');

        return $sessions
            ->groupBy(fn (AnalyticsSession $session): string => $session->source_label ?: $session->utm_source ?: $session->referrer_domain ?: 'direct')
            ->map(function (Collection $items, string $source) use ($since, $primarySourceByCustomer): array {
                $allCustomerIds = $items->pluck('customer_id')->filter()->unique();
                $customerIds = $allCustomerIds
                    ->filter(fn ($customerId): bool => ($primarySourceByCustomer->get($customerId) ?? 'direct') === $source)
                    ->values();
                $ticketRevenue = TicketOrder::query()
                    ->whereIn('customer_id', $customerIds)
                    ->where('status', 'paid')
                    ->where(function ($query) use ($since): void {
                        $query->where('paid_at', '>=', $since)
                            ->orWhere(fn ($fallback) => $fallback->whereNull('paid_at')->where('created_at', '>=', $since));
                    })
                    ->sum('total');
                $bookingRevenue = ContentBooking::query()
                    ->whereIn('customer_id', $customerIds)
                    ->where('status', 'confirmed')
                    ->where(function ($query) use ($since): void {
                        $query->where('paid_at', '>=', $since)
                            ->orWhere(fn ($fallback) => $fallback->whereNull('paid_at')->where('created_at', '>=', $since));
                    })
                    ->sum('amount');

                return [
                    'source' => $source,
                    'channel' => $items->first()?->source_type ?: 'direct',
                    'sessions' => $items->count(),
                    'visitors' => $items->pluck('visitor_id')->filter()->unique()->count(),
                    'identified_leads' => $allCustomerIds->count(),
                    'customer_ids' => $allCustomerIds->values()->all(),
                    'revenue' => (float) $ticketRevenue + (float) $bookingRevenue,
                    'lead_rate' => $items->count() > 0 ? round(($allCustomerIds->count() / $items->count()) * 100, 1) : 0,
                ];
            })
            ->sortByDesc('revenue')
            ->take(10)
            ->values()
            ->all();
    }

    protected function dropoffs(Collection $events): array
    {
        $watchedNames = [
            'booking_popup_dismissed' => 'Popup cerrado',
            'booking_abandoned' => 'Booking abandonado',
            'booking_payment_failed' => 'Pago booking fallido',
            'payment_failed' => 'Pago ticket fallido',
            'page_exit' => 'Salida de página',
        ];

        return $events
            ->filter(fn (AnalyticsEvent $event): bool => array_key_exists($event->name, $watchedNames))
            ->groupBy('name')
            ->map(fn (Collection $items, string $name): array => [
                'stage' => $watchedNames[$name],
                'events' => $items->count(),
                'visitors' => $items->pluck('visitor_id')->filter()->unique()->count(),
            ])
            ->sortByDesc('events')
            ->values()
            ->all();
    }

    protected function repeatCustomers(Collection $customers): int
    {
        return $customers
            ->filter(function (Customer $customer): bool {
                $paidOrders = (int) $customer->paid_ticket_orders_count;
                $bookings = (int) $customer->confirmed_content_bookings_count;

                return ($paidOrders + $bookings) > 1;
            })
            ->count();
    }
}
