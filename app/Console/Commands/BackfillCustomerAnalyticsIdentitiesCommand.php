<?php

namespace App\Console\Commands;

use App\Models\AnalyticsEvent;
use App\Models\AnalyticsPageview;
use App\Models\AnalyticsSession;
use App\Models\AnalyticsVisitorIdentity;
use App\Models\ContentBooking;
use App\Models\Customer;
use App\Models\TicketOrder;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class BackfillCustomerAnalyticsIdentitiesCommand extends Command
{
    protected $signature = 'analytics:backfill-customer-identities
        {--dry-run : Report changes without writing to the database}
        {--limit= : Maximum records per source to scan}
        {--since= : Only scan source records created since this date}';

    protected $description = 'Backfill analytics visitor identities and customer links from historical lead, ticket and booking metadata.';

    /**
     * @var array<string, int>
     */
    protected array $stats = [
        'candidates_scanned' => 0,
        'identities_created' => 0,
        'identities_updated' => 0,
        'sessions_updated' => 0,
        'pageviews_updated' => 0,
        'events_updated' => 0,
        'skipped' => 0,
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $limit = $this->option('limit') !== null ? max((int) $this->option('limit'), 1) : null;
        $since = $this->option('since') ? Carbon::parse((string) $this->option('since')) : null;

        $this->processCustomers($dryRun, $limit, $since);
        $this->processTicketOrders($dryRun, $limit, $since);
        $this->processContentBookings($dryRun, $limit, $since);

        $this->table(
            ['metric', 'count'],
            collect($this->stats)->map(fn (int $count, string $metric): array => [$metric, $count])->values()->all(),
        );

        if ($dryRun) {
            $this->info('Dry run complete. No analytics rows were changed.');
        }

        return self::SUCCESS;
    }

    protected function processCustomers(bool $dryRun, ?int $limit, ?Carbon $since): void
    {
        $this->query(Customer::query(), $limit, $since)
            ->select(['id', 'metadata', 'created_at'])
            ->get()
            ->each(function (Customer $customer) use ($dryRun): void {
                $metadata = is_array($customer->metadata) ? $customer->metadata : [];
                $this->linkCandidate((int) $customer->id, $metadata, 'customer_metadata', $dryRun);
                $this->linkCandidate((int) $customer->id, $this->arrayData(data_get($metadata, 'popup_capture', [])), 'customer_popup_capture', $dryRun);
                $this->linkCandidate((int) $customer->id, $this->arrayData(data_get($metadata, 'guestlist_registration', [])), 'customer_guestlist_registration', $dryRun);
            });
    }

    protected function processTicketOrders(bool $dryRun, ?int $limit, ?Carbon $since): void
    {
        $this->query(TicketOrder::query()->whereNotNull('customer_id'), $limit, $since)
            ->select(['id', 'customer_id', 'metadata', 'created_at'])
            ->get()
            ->each(function (TicketOrder $order) use ($dryRun): void {
                $this->linkCandidate(
                    (int) $order->customer_id,
                    is_array($order->metadata) ? $order->metadata : [],
                    'ticket_order',
                    $dryRun,
                );
            });
    }

    protected function processContentBookings(bool $dryRun, ?int $limit, ?Carbon $since): void
    {
        $this->query(ContentBooking::query()->whereNotNull('customer_id'), $limit, $since)
            ->select(['id', 'customer_id', 'analytics_visitor_id', 'analytics_session_id', 'metadata', 'created_at'])
            ->get()
            ->each(function (ContentBooking $booking) use ($dryRun): void {
                $this->linkCandidate(
                    (int) $booking->customer_id,
                    [
                        'analytics_visitor_id' => $booking->analytics_visitor_id,
                        'analytics_session_id' => $booking->analytics_session_id,
                    ],
                    'content_booking',
                    $dryRun,
                );
                $this->linkCandidate(
                    (int) $booking->customer_id,
                    is_array($booking->metadata) ? $booking->metadata : [],
                    'content_booking_metadata',
                    $dryRun,
                );
            });
    }

    /**
     * @template TModel of \Illuminate\Database\Eloquent\Model
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    protected function query(Builder $query, ?int $limit, ?Carbon $since): Builder
    {
        if ($since) {
            $query->where('created_at', '>=', $since);
        }

        if ($limit) {
            $query->limit($limit);
        }

        return $query->orderBy('id');
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    protected function linkCandidate(int $customerId, array $metadata, string $source, bool $dryRun): void
    {
        $this->stats['candidates_scanned']++;

        $session = $this->findSession(data_get($metadata, 'analytics_session_id'));
        $visitorId = $this->validUuid(data_get($metadata, 'analytics_visitor_id'))
            ? (string) data_get($metadata, 'analytics_visitor_id')
            : $session?->visitor_id;

        if (! $visitorId) {
            $this->stats['skipped']++;

            return;
        }

        $sessionIds = AnalyticsSession::query()
            ->where('visitor_id', $visitorId)
            ->when($session, fn ($query) => $query->orWhere('id', $session->id))
            ->pluck('id');

        if ($dryRun) {
            $exists = AnalyticsVisitorIdentity::query()->where('visitor_id', $visitorId)->exists();
            $this->stats[$exists ? 'identities_updated' : 'identities_created']++;
            $this->stats['sessions_updated'] += AnalyticsSession::query()
                ->whereKey($sessionIds)
                ->where(fn ($query) => $query->whereNull('customer_id')->orWhere('customer_id', '!=', $customerId))
                ->count();
            $this->stats['pageviews_updated'] += AnalyticsPageview::query()
                ->where(fn ($query) => $query->whereIn('analytics_session_id', $sessionIds)->orWhere('visitor_id', $visitorId))
                ->where(fn ($query) => $query->whereNull('customer_id')->orWhere('customer_id', '!=', $customerId))
                ->count();
            $this->stats['events_updated'] += AnalyticsEvent::query()
                ->where(fn ($query) => $query->whereIn('analytics_session_id', $sessionIds)->orWhere('visitor_id', $visitorId))
                ->where(fn ($query) => $query->whereNull('customer_id')->orWhere('customer_id', '!=', $customerId))
                ->count();

            return;
        }

        $identity = AnalyticsVisitorIdentity::query()->where('visitor_id', $visitorId)->first();
        $identity
            ? $this->stats['identities_updated']++
            : $this->stats['identities_created']++;

        AnalyticsVisitorIdentity::query()->updateOrCreate(
            ['visitor_id' => $visitorId],
            [
                'customer_id' => $customerId,
                'source' => $source,
                'first_linked_at' => $identity?->first_linked_at ?? now(),
                'last_seen_at' => now(),
            ],
        );

        $this->stats['sessions_updated'] += AnalyticsSession::query()
            ->whereKey($sessionIds)
            ->where(fn ($query) => $query->whereNull('customer_id')->orWhere('customer_id', '!=', $customerId))
            ->update(['customer_id' => $customerId]);

        $this->stats['pageviews_updated'] += AnalyticsPageview::query()
            ->where(fn ($query) => $query->whereIn('analytics_session_id', $sessionIds)->orWhere('visitor_id', $visitorId))
            ->where(fn ($query) => $query->whereNull('customer_id')->orWhere('customer_id', '!=', $customerId))
            ->update(['customer_id' => $customerId]);

        $this->stats['events_updated'] += AnalyticsEvent::query()
            ->where(fn ($query) => $query->whereIn('analytics_session_id', $sessionIds)->orWhere('visitor_id', $visitorId))
            ->where(fn ($query) => $query->whereNull('customer_id')->orWhere('customer_id', '!=', $customerId))
            ->update(['customer_id' => $customerId]);
    }

    protected function findSession(mixed $sessionId): ?AnalyticsSession
    {
        if (! $this->validUuid($sessionId)) {
            return null;
        }

        return AnalyticsSession::query()
            ->where('session_id', (string) $sessionId)
            ->first();
    }

    /**
     * @return array<string, mixed>
     */
    protected function arrayData(mixed $value): array
    {
        return is_array($value) ? $value : [];
    }

    protected function validUuid(mixed $value): bool
    {
        return is_string($value)
            && (bool) preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $value);
    }
}
