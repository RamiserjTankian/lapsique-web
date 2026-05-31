<?php

namespace App\Services;

use App\Models\AnalyticsEvent;
use App\Models\AnalyticsPageview;
use App\Models\AnalyticsSession;
use App\Models\AnalyticsVisitorIdentity;
use App\Models\Customer;
use Illuminate\Support\Carbon;

class CustomerAnalyticsAttributionService
{
    public function identify(Customer $customer, ?string $visitorId = null, ?string $sessionId = null, string $source = 'customer_identified'): void
    {
        $session = $this->findSession($sessionId);
        $visitorId = $this->validUuid($visitorId) ? $visitorId : $session?->visitor_id;

        if (! $visitorId) {
            return;
        }

        $now = now();

        AnalyticsVisitorIdentity::query()->updateOrCreate(
            ['visitor_id' => $visitorId],
            [
                'customer_id' => $customer->id,
                'source' => $source,
                'first_linked_at' => AnalyticsVisitorIdentity::query()
                    ->where('visitor_id', $visitorId)
                    ->value('first_linked_at') ?? $now,
                'last_seen_at' => $now,
            ],
        );

        $sessionIds = AnalyticsSession::query()
            ->where(function ($query) use ($visitorId, $session): void {
                $query->where('visitor_id', $visitorId);

                if ($session) {
                    $query->orWhere('id', $session->id);
                }
            })
            ->pluck('id');

        if ($sessionIds->isEmpty()) {
            return;
        }

        AnalyticsSession::query()
            ->whereKey($sessionIds)
            ->update(['customer_id' => $customer->id]);

        AnalyticsPageview::query()
            ->whereIn('analytics_session_id', $sessionIds)
            ->orWhere('visitor_id', $visitorId)
            ->update(['customer_id' => $customer->id]);

        AnalyticsEvent::query()
            ->whereIn('analytics_session_id', $sessionIds)
            ->orWhere('visitor_id', $visitorId)
            ->update(['customer_id' => $customer->id]);

        app(CustomerJourneyInsightsService::class)->clearCache();
    }

    public function resolveCustomerId(?string $visitorId = null, ?string $sessionId = null): ?int
    {
        $session = $this->findSession($sessionId);

        if ($session?->customer_id) {
            return (int) $session->customer_id;
        }

        $visitorId = $this->validUuid($visitorId) ? $visitorId : $session?->visitor_id;

        if (! $visitorId) {
            return null;
        }

        $identity = AnalyticsVisitorIdentity::query()
            ->where('visitor_id', $visitorId)
            ->first();

        if (! $identity) {
            return null;
        }

        $identity->forceFill(['last_seen_at' => Carbon::now()])->save();

        return (int) $identity->customer_id;
    }

    protected function findSession(?string $sessionId): ?AnalyticsSession
    {
        if (! $this->validUuid($sessionId)) {
            return null;
        }

        return AnalyticsSession::query()
            ->where('session_id', $sessionId)
            ->first();
    }

    protected function validUuid(?string $value): bool
    {
        return is_string($value)
            && (bool) preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $value);
    }
}
