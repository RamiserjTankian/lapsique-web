<?php

namespace App\Support;

use App\Models\AnalyticsEvent;
use App\Models\AnalyticsPageview;
use App\Models\AnalyticsSession;
use App\Models\Event;
use App\Models\TicketOrder;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class EventSalesInsights
{
    protected ?Collection $entrySessions = null;

    protected ?Collection $entryPageviews = null;

    protected ?Collection $relatedEvents = null;

    protected ?Collection $entryAttributionEvents = null;

    protected ?Collection $paidOrders = null;

    protected array $visitorIdsCache = [];

    protected array $stepEventsCache = [];

    protected ?array $summary = null;

    public function __construct(protected TicketOrder $record)
    {
    }

    public function event(): ?Event
    {
        return $this->record->event;
    }

    public function eventPath(): ?string
    {
        $event = $this->event();

        if (! $event) {
            return null;
        }

        return parse_url(route('events.show', $event, false), PHP_URL_PATH) ?: null;
    }

    public function checkoutPath(): ?string
    {
        $event = $this->event();

        if (! $event) {
            return null;
        }

        return parse_url(route('tickets.checkout.show', $event, false), PHP_URL_PATH) ?: null;
    }

    public function trackedPaths(): array
    {
        return collect([
            $this->eventPath(),
            $this->checkoutPath(),
        ])->filter()->unique()->values()->all();
    }

    public function summary(): array
    {
        if ($this->summary !== null) {
            return $this->summary;
        }

        $entryVisitors = $this->entrySessions()
            ->pluck('visitor_id')
            ->filter()
            ->unique()
            ->count();
        $ticketVisitors = $this->visitorIdsFor('tickets_section')->count();
        $cartVisitors = $this->visitorIdsFor('added_to_cart')->count();
        $checkoutStartedVisitors = $this->visitorIdsFor('checkout_started')->count();
        $checkoutSubmittedVisitors = $this->visitorIdsFor('checkout_submitted')->count();
        $paidVisitors = $this->visitorIdsFor('paid')->count();
        $paidCustomers = $this->paidOrders()
            ->pluck('buyer_email')
            ->filter()
            ->unique()
            ->count();

        $paidOrders = (int) ($this->record->orders_count ?? 0);
        $pageviews = $this->entryVisitsCount();
        $sessions = $this->entrySessions()->count();

        $this->summary = [
            'entry_visitors' => $entryVisitors,
            'entry_pageviews' => $pageviews,
            'entry_sessions' => $sessions,
            'ticket_visitors' => $ticketVisitors,
            'cart_visitors' => $cartVisitors,
            'checkout_started_visitors' => $checkoutStartedVisitors,
            'checkout_submitted_visitors' => $checkoutSubmittedVisitors,
            'paid_visitors' => $paidVisitors,
            'paid_customers' => $paidCustomers,
            'paid_orders' => $paidOrders,
            'tickets_sold' => (int) ($this->record->tickets_sold ?? 0),
            'tickets_registered' => (int) ($this->record->tickets_registered ?? 0),
            'revenue_total' => (float) ($this->record->revenue_total ?? 0),
            'revenue_subtotal' => (float) ($this->record->revenue_subtotal ?? 0),
            'revenue_fee' => (float) ($this->record->revenue_fee ?? 0),
            'ticket_section_rate' => $this->rate($ticketVisitors, $entryVisitors),
            'cart_rate' => $this->rate($cartVisitors, $entryVisitors),
            'checkout_started_rate' => $this->rate($checkoutStartedVisitors, $entryVisitors),
            'checkout_submitted_rate' => $this->rate($checkoutSubmittedVisitors, $entryVisitors),
            'visitor_to_paid_rate' => $this->rate($paidVisitors, $entryVisitors),
            'order_to_visitor_rate' => $this->rate($paidOrders, $entryVisitors),
        ];

        return $this->summary;
    }

    public function funnelRows(): array
    {
        $summary = $this->summary();

        $rows = [
            ['label' => 'Entran al evento', 'count' => $summary['entry_visitors']],
            ['label' => 'Ven accesos', 'count' => $summary['ticket_visitors']],
            ['label' => 'Preparan compra', 'count' => $summary['cart_visitors']],
            ['label' => 'Inician checkout', 'count' => $summary['checkout_started_visitors']],
            ['label' => 'Envían checkout', 'count' => $summary['checkout_submitted_visitors']],
            ['label' => 'Terminan pagando', 'count' => $summary['paid_visitors']],
        ];

        $entryVisitors = max(1, $summary['entry_visitors']);
        $previous = null;

        return collect($rows)
            ->map(function (array $row) use (&$previous, $entryVisitors): array {
                $count = (int) $row['count'];
                $fromPrevious = $previous === null ? 100.0 : $this->rate($count, max(1, $previous));
                $fromEntry = $this->rate($count, $entryVisitors);
                $previous = $count;

                return [
                    'label' => $row['label'],
                    'count' => $count,
                    'from_previous' => $fromPrevious,
                    'from_entry' => $fromEntry,
                ];
            })
            ->all();
    }

    public function sourceBreakdown(int $limit = 8): Collection
    {
        $entryVisitors = $this->entrySessions();
        $ticketVisitorIds = $this->visitorIdsFor('tickets_section');
        $cartVisitorIds = $this->visitorIdsFor('added_to_cart');
        $checkoutVisitorIds = $this->visitorIdsFor('checkout_started');
        $paidVisitorIds = $this->visitorIdsFor('paid');

        return $entryVisitors
            ->groupBy(fn (AnalyticsSession $session): string => $this->sourceLabel($session))
            ->map(function (Collection $sessions, string $source) use ($ticketVisitorIds, $cartVisitorIds, $checkoutVisitorIds, $paidVisitorIds): array {
                $visitorIds = $sessions->pluck('visitor_id')->filter()->unique()->values();
                $visitors = $visitorIds->count();
                $tickets = $visitorIds->intersect($ticketVisitorIds)->count();
                $cart = $visitorIds->intersect($cartVisitorIds)->count();
                $checkout = $visitorIds->intersect($checkoutVisitorIds)->count();
                $paid = $visitorIds->intersect($paidVisitorIds)->count();

                return [
                    'source' => $source,
                    'visitors' => $visitors,
                    'tickets' => $tickets,
                    'cart' => $cart,
                    'checkout' => $checkout,
                    'paid' => $paid,
                    'conversion' => $this->rate($paid, $visitors),
                ];
            })
            ->sortByDesc('visitors')
            ->take($limit)
            ->values();
    }

    public function deviceBreakdown(): Collection
    {
        return $this->entrySessions()
            ->groupBy(fn (AnalyticsSession $session): string => $this->deviceLabel($session->device_type))
            ->map(fn (Collection $sessions, string $device): array => [
                'device' => $device,
                'visitors' => $sessions->pluck('visitor_id')->filter()->unique()->count(),
            ])
            ->sortByDesc('visitors')
            ->values();
    }

    public function referrerBreakdown(int $limit = 6): Collection
    {
        return $this->entrySessions()
            ->groupBy(fn (AnalyticsSession $session): string => $session->referrer_domain ?: 'Directo')
            ->map(fn (Collection $sessions, string $referrer): array => [
                'referrer' => $referrer,
                'visitors' => $sessions->pluck('visitor_id')->filter()->unique()->count(),
            ])
            ->sortByDesc('visitors')
            ->take($limit)
            ->values();
    }

    public function timeline(int $days = 21): array
    {
        $eventPath = $this->eventPath();

        if (! $eventPath) {
            return [
                'labels' => [],
                'visitors' => [],
                'cart' => [],
                'checkout' => [],
                'paid' => [],
                'revenue' => [],
            ];
        }

        $end = $this->latestActivityAt()?->endOfDay() ?? now()->endOfDay();
        $start = (clone $end)->subDays($days - 1)->startOfDay();

        $visitorSeries = $this->entryPageviews()
            ->filter(fn (AnalyticsPageview $pageview): bool => $pageview->created_at?->between($start, $end))
            ->groupBy(fn (AnalyticsPageview $pageview): string => $pageview->created_at->toDateString())
            ->map(fn (Collection $pageviews): int => $pageviews->pluck('visitor_id')->filter()->unique()->count());

        $cartSeries = $this->stepEvents('added_to_cart')
            ->filter(fn (AnalyticsEvent $event): bool => $event->created_at?->between($start, $end))
            ->groupBy(fn (AnalyticsEvent $event): string => $event->created_at->toDateString())
            ->map(fn (Collection $events): int => $events->pluck('visitor_id')->filter()->unique()->count());

        $checkoutSeries = $this->stepEvents('checkout_started')
            ->filter(fn (AnalyticsEvent $event): bool => $event->created_at?->between($start, $end))
            ->groupBy(fn (AnalyticsEvent $event): string => $event->created_at->toDateString())
            ->map(fn (Collection $events): int => $events->pluck('visitor_id')->filter()->unique()->count());

        $paidSeries = $this->paidOrdersQuery()
            ->whereNotNull('paid_at')
            ->whereBetween('paid_at', [$start, $end])
            ->selectRaw('DATE(paid_at) as date, COUNT(*) as count, SUM(total) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $labels = [];
        $visitors = [];
        $cart = [];
        $checkout = [];
        $paid = [];
        $revenue = [];

        for ($cursor = (clone $start); $cursor->lte($end); $cursor->addDay()) {
            $date = $cursor->format('Y-m-d');
            $labels[] = $cursor->translatedFormat('d M');
            $visitors[] = (int) ($visitorSeries[$date] ?? 0);
            $cart[] = (int) ($cartSeries[$date] ?? 0);
            $checkout[] = (int) ($checkoutSeries[$date] ?? 0);
            $paid[] = (int) data_get($paidSeries, "{$date}.count", 0);
            $revenue[] = round((float) data_get($paidSeries, "{$date}.revenue", 0), 2);
        }

        return compact('labels', 'visitors', 'cart', 'checkout', 'paid', 'revenue');
    }

    public function paidOrdersQuery(): Builder
    {
        $event = $this->event();

        if (! $event) {
            return TicketOrder::query()->whereRaw('1 = 0');
        }

        return TicketOrder::query()
            ->where('event_id', $event->id)
            ->where('status', 'paid');
    }

    public function entrySessions(): Collection
    {
        if ($this->entrySessions !== null) {
            return $this->entrySessions;
        }

        $eventPath = $this->eventPath();
        $sessionIds = collect();

        if ($eventPath) {
            $sessionIds = $sessionIds->merge(
                AnalyticsPageview::query()
                    ->where('path', $eventPath)
                    ->pluck('analytics_session_id')
            );
        }

        $sessionIds = $sessionIds
            ->merge($this->entryAttributionEvents()->pluck('analytics_session_id'))
            ->filter()
            ->unique()
            ->values();

        if ($sessionIds->isEmpty()) {
            $this->entrySessions = collect();

            return $this->entrySessions;
        }

        $this->entrySessions = AnalyticsSession::query()
            ->whereIn('id', $sessionIds)
            ->orderBy('created_at')
            ->get()
            ->unique('id')
            ->values();

        return $this->entrySessions;
    }

    protected function paidOrders(): Collection
    {
        if ($this->paidOrders !== null) {
            return $this->paidOrders;
        }

        $this->paidOrders = $this->paidOrdersQuery()->get();

        return $this->paidOrders;
    }

    protected function visitorIdsFor(string $step): Collection
    {
        if (array_key_exists($step, $this->visitorIdsCache)) {
            return $this->visitorIdsCache[$step];
        }

        $visitorIds = match ($step) {
            'entered' => $this->entrySessions()->pluck('visitor_id')->filter()->unique()->values(),
            'tickets_section', 'added_to_cart', 'checkout_started', 'checkout_submitted' => $this->stepEvents($step)
                ->pluck('visitor_id')
                ->filter()
                ->unique()
                ->values(),
            'paid' => $this->paidVisitorIds(),
            default => collect(),
        };

        return $this->visitorIdsCache[$step] = $visitorIds;
    }

    protected function interactionQuery(array $names): Builder
    {
        $eventId = $this->eventId();
        $paths = $this->trackedPaths();
        $entrySessionIds = $this->entrySessions()->pluck('id')->all();

        return AnalyticsEvent::query()
            ->whereIn('name', $names)
            ->where(function (Builder $query) use ($paths, $eventId, $entrySessionIds): void {
                if ($paths !== []) {
                    $query->whereIn('path', $paths);
                }

                if ($eventId !== '') {
                    $query->orWhere('metadata->event_id', $eventId);
                }

                if ($entrySessionIds !== []) {
                    $query->orWhereIn('analytics_session_id', $entrySessionIds);
                }
            });
    }

    protected function pageviewsQuery(): Builder
    {
        $pageviewIds = $this->entryAttributionEvents()
            ->pluck('analytics_pageview_id')
            ->filter()
            ->unique()
            ->all();
        $eventPath = $this->eventPath();

        if (! $eventPath && $pageviewIds === []) {
            return AnalyticsPageview::query()->whereRaw('1 = 0');
        }

        return AnalyticsPageview::query()
            ->where(function (Builder $query) use ($eventPath, $pageviewIds): void {
                if ($eventPath) {
                    $query->where('path', $eventPath);
                }

                if ($pageviewIds !== []) {
                    $query->orWhereIn('id', $pageviewIds);
                }
            });
    }

    protected function latestActivityAt(): ?Carbon
    {
        $candidates = collect([
            $this->entryPageviews()->max('created_at'),
            $this->relatedEvents()->max('created_at'),
            $this->paidOrdersQuery()->max('paid_at'),
            optional($this->record->last_paid_at)?->toDateTimeString(),
        ])->filter();

        if ($candidates->isEmpty()) {
            return null;
        }

        return Carbon::parse($candidates->max());
    }

    protected function sourceLabel(AnalyticsSession $session): string
    {
        if ($session->utm_source) {
            return trim($session->utm_source . ($session->utm_medium ? ' / ' . $session->utm_medium : ''));
        }

        if ($session->referrer_domain) {
            return $session->referrer_domain;
        }

        return 'Directo';
    }

    protected function deviceLabel(?string $device): string
    {
        return match ($device) {
            'mobile' => 'Móvil',
            'tablet' => 'Tablet',
            'desktop' => 'Desktop',
            default => 'Sin detectar',
        };
    }

    protected function rate(int|float $value, int|float $total): float
    {
        if ((float) $total <= 0) {
            return 0.0;
        }

        return round((((float) $value) / ((float) $total)) * 100, 1);
    }

    protected function eventId(): string
    {
        return (string) optional($this->event())->id;
    }

    protected function entryAttributionEvents(): Collection
    {
        if ($this->entryAttributionEvents !== null) {
            return $this->entryAttributionEvents;
        }

        $eventId = $this->eventId();

        if ($eventId === '') {
            $this->entryAttributionEvents = collect();

            return $this->entryAttributionEvents;
        }

        $this->entryAttributionEvents = AnalyticsEvent::query()
            ->whereIn('name', ['landing_viewed', 'event_viewed'])
            ->where('metadata->event_id', $eventId)
            ->orderBy('created_at')
            ->get();

        return $this->entryAttributionEvents;
    }

    protected function entryPageviews(): Collection
    {
        if ($this->entryPageviews !== null) {
            return $this->entryPageviews;
        }

        $this->entryPageviews = $this->pageviewsQuery()
            ->orderBy('created_at')
            ->get()
            ->unique('id')
            ->values();

        return $this->entryPageviews;
    }

    protected function entryVisitsCount(): int
    {
        $trackedPageviews = $this->entryPageviews()->count();

        $fallbackVisits = $this->entryAttributionEvents()
            ->map(function (AnalyticsEvent $event): ?string {
                if ($event->analytics_pageview_id) {
                    return 'pageview:' . $event->analytics_pageview_id;
                }

                if ($event->analytics_session_id) {
                    return 'session:' . $event->analytics_session_id;
                }

                return null;
            })
            ->filter()
            ->unique()
            ->count();

        return max($trackedPageviews, $fallbackVisits);
    }

    protected function relatedEvents(): Collection
    {
        if ($this->relatedEvents !== null) {
            return $this->relatedEvents;
        }

        $this->relatedEvents = $this->interactionQuery([
            'landing_viewed',
            'event_viewed',
            'section_view',
            'reservation_modal_opened',
            'tickets_added_to_cart',
            'landing_cart_updated',
            'landing_checkout_started',
            'checkout_started',
            'landing_checkout_submitted',
            'checkout_submitted',
            'purchase_completed',
        ])->orderBy('created_at')->get();

        return $this->relatedEvents;
    }

    protected function stepEvents(string $step): Collection
    {
        if (array_key_exists($step, $this->stepEventsCache)) {
            return $this->stepEventsCache[$step];
        }

        $events = $this->relatedEvents()->filter(function (AnalyticsEvent $event) use ($step): bool {
            return match ($step) {
                'tickets_section' => ($event->name === 'section_view' && $event->label === 'tickets')
                    || $event->name === 'reservation_modal_opened',
                'added_to_cart' => in_array($event->name, ['tickets_added_to_cart', 'landing_cart_updated', 'landing_checkout_started'], true),
                'checkout_started' => in_array($event->name, ['checkout_started', 'landing_checkout_started'], true),
                'checkout_submitted' => in_array($event->name, ['checkout_submitted', 'landing_checkout_submitted'], true),
                default => false,
            };
        })->values();

        return $this->stepEventsCache[$step] = $events;
    }

    protected function paidVisitorIds(): Collection
    {
        $visitorIds = $this->paidOrders()
            ->pluck('metadata')
            ->map(fn ($metadata) => data_get($metadata, 'analytics_visitor_id'))
            ->filter();

        $missingOrderIds = $this->paidOrders()
            ->filter(fn (TicketOrder $order): bool => blank(data_get($order->metadata, 'analytics_visitor_id')))
            ->pluck('public_id')
            ->filter()
            ->values();

        if ($missingOrderIds->isNotEmpty()) {
            $purchaseVisitors = $this->relatedEvents()
                ->where('name', 'purchase_completed')
                ->filter(fn (AnalyticsEvent $event): bool => $missingOrderIds->contains(data_get($event->metadata, 'order_id')))
                ->pluck('visitor_id')
                ->filter();

            $visitorIds = $visitorIds->merge($purchaseVisitors);
        }

        return $visitorIds->unique()->values();
    }
}
