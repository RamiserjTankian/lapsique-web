<?php

namespace App\Services;

use App\Models\AnalyticsEvent;
use App\Models\AnalyticsPageview;
use App\Models\AnalyticsSession;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AnalyticsRealtimeService
{
    public function __construct(
        protected AnalyticsSessionEnrichmentService $sessionEnrichmentService
    ) {
    }

    public function snapshot(): array
    {
        $now = now();
        $activeWindowSeconds = max((int) config('analytics.presence.active_window_seconds', 45), 15);
        $recentWindowMinutes = max((int) config('analytics.presence.recent_window_minutes', 15), 5);
        $activeSince = $now->copy()->subSeconds($activeWindowSeconds);
        $recentSince = $now->copy()->subMinutes($recentWindowMinutes);

        $sessions = AnalyticsSession::query()
            ->where(function ($query) use ($recentSince) {
                $query
                    ->where('created_at', '>=', $recentSince)
                    ->orWhere('last_seen_at', '>=', $recentSince);
            })
            ->withCount(['pageviews', 'events'])
            ->with([
                'pageviews' => fn ($query) => $query->orderBy('created_at'),
                'events' => fn ($query) => $query->orderBy('created_at'),
            ])
            ->orderByDesc('last_seen_at')
            ->orderByDesc('created_at')
            ->get();

        $activeSessions = $sessions
            ->filter(fn (AnalyticsSession $session): bool => $this->sessionLastSeenAt($session)?->greaterThanOrEqualTo($activeSince) ?? false)
            ->values();

        $recentEntries = $sessions
            ->filter(fn (AnalyticsSession $session): bool => $session->created_at?->greaterThanOrEqualTo($recentSince) ?? false)
            ->sortByDesc('created_at')
            ->values();

        $recentExits = $sessions
            ->filter(function (AnalyticsSession $session) use ($activeSince, $recentSince): bool {
                $lastSeenAt = $this->sessionLastSeenAt($session);

                if (! $lastSeenAt) {
                    return false;
                }

                return $lastSeenAt->lessThan($activeSince) && $lastSeenAt->greaterThanOrEqualTo($recentSince);
            })
            ->sortByDesc(fn (AnalyticsSession $session) => $this->sessionLastSeenAt($session))
            ->values();

        $durations = $sessions
            ->map(fn (AnalyticsSession $session): int => $this->durationInSeconds($session, $now, $activeSince))
            ->filter(fn (int $seconds): bool => $seconds > 0);

        $avgDurationSeconds = $durations->isNotEmpty() ? (int) round($durations->avg()) : 0;

        return [
            'generated_at' => $now,
            'active_window_seconds' => $activeWindowSeconds,
            'recent_window_minutes' => $recentWindowMinutes,
            'polling_interval' => (string) config('analytics.presence.dashboard_polling_interval', '10s'),
            'stats' => [
                'active_sessions' => $activeSessions->count(),
                'active_visitors' => $activeSessions->pluck('visitor_id')->filter()->unique()->count(),
                'recent_entries' => $recentEntries->count(),
                'recent_exits' => $recentExits->count(),
                'avg_duration_seconds' => $avgDurationSeconds,
                'avg_duration_human' => $this->humanDuration($avgDurationSeconds),
            ],
            'current_pages' => $activeSessions
                ->map(fn (AnalyticsSession $session): string => $this->currentPath($session))
                ->filter()
                ->countBy()
                ->sortDesc()
                ->take(6)
                ->map(fn (int $count, string $path): array => [
                    'path' => $path,
                    'sessions' => $count,
                ])
                ->values()
                ->all(),
            'active_sessions_list' => $this->mapSessions($activeSessions->take(10), $now, $activeSince, true),
            'recent_entries_list' => $this->mapSessions($recentEntries->take(8), $now, $activeSince),
            'recent_exits_list' => $this->mapSessions($recentExits->take(8), $now, $activeSince),
        ];
    }

    protected function mapSessions(Collection $sessions, CarbonInterface $now, CarbonInterface $activeSince, ?bool $forceActive = null): array
    {
        return $sessions
            ->map(function (AnalyticsSession $session) use ($now, $activeSince, $forceActive): array {
                $lastSeenAt = $this->sessionLastSeenAt($session);
                $isActive = $forceActive ?? ($lastSeenAt?->greaterThanOrEqualTo($activeSince) ?? false);
                $durationSeconds = $this->durationInSeconds($session, $now, $activeSince);
                $pageviews = $session->pageviews->sortBy('created_at')->values();
                $latestPageview = $pageviews->last();
                $latestEvent = $session->events->sortBy('created_at')->last();
                $source = $session->source_label
                    ? ['source_label' => $session->source_label, 'source_type' => $session->source_type]
                    : $this->sessionEnrichmentService->resolveSource(
                        $session->utm_source,
                        $session->utm_medium,
                        $session->referrer_domain,
                        $session->landing_url
                    );
                $journey = $this->journey($pageviews);

                return [
                    'session_id' => $session->session_id,
                    'visitor_id' => $session->visitor_id,
                    'is_active' => $isActive,
                    'source_label' => $source['source_label'] ?? 'direct',
                    'source_type' => $source['source_type'] ?? 'direct',
                    'landing_path' => $session->landing_path ?: '/',
                    'current_path' => $this->currentPath($session),
                    'current_title' => $latestPageview?->title,
                    'pageviews_count' => (int) $session->pageviews_count,
                    'events_count' => (int) $session->events_count,
                    'duration_seconds' => $durationSeconds,
                    'duration_human' => $this->humanDuration($durationSeconds),
                    'started_at' => $session->created_at,
                    'last_seen_at' => $lastSeenAt,
                    'location' => $this->locationLabel($session),
                    'device_type' => $session->device_type ?: 'desktop',
                    'browser' => $session->browser ?: 'n/d',
                    'journey' => $journey,
                    'journey_label' => collect($journey)->join(' -> '),
                    'last_event_name' => $latestEvent?->name,
                    'last_event_label' => $latestEvent?->label,
                ];
            })
            ->values()
            ->all();
    }

    protected function durationInSeconds(AnalyticsSession $session, CarbonInterface $now, CarbonInterface $activeSince): int
    {
        $lastSeenAt = $this->sessionLastSeenAt($session);
        $endedAt = $lastSeenAt?->greaterThanOrEqualTo($activeSince) ? $now : ($lastSeenAt ?: $session->created_at);

        return max($session->created_at?->diffInSeconds($endedAt) ?? 0, 1);
    }

    protected function sessionLastSeenAt(AnalyticsSession $session): ?CarbonInterface
    {
        if ($session->last_seen_at) {
            return $session->last_seen_at;
        }

        $timestamps = array_filter([
            $session->created_at,
            $session->pageviews->max('created_at'),
            $session->events->max('created_at'),
        ]);

        if ($timestamps === []) {
            return null;
        }

        return collect($timestamps)
            ->map(fn ($value) => $value instanceof CarbonInterface ? $value : Carbon::parse($value))
            ->sortByDesc(fn (CarbonInterface $timestamp) => $timestamp->getTimestamp())
            ->first();
    }

    protected function currentPath(AnalyticsSession $session): string
    {
        /** @var AnalyticsEvent|null $latestEvent */
        $latestEvent = $session->events->sortBy('created_at')->last();
        /** @var AnalyticsPageview|null $latestPageview */
        $latestPageview = $session->pageviews->sortBy('created_at')->last();

        return $latestEvent?->path ?: $latestPageview?->path ?: ($session->landing_path ?: '/');
    }

    protected function journey(Collection $pageviews): array
    {
        $paths = [];

        foreach ($pageviews as $pageview) {
            $path = $pageview->path ?: '/';

            if ($path === end($paths)) {
                continue;
            }

            $paths[] = $path;
        }

        return array_slice($paths, 0, 6);
    }

    protected function locationLabel(AnalyticsSession $session): string
    {
        $parts = array_filter([
            $session->city,
            $session->region ?: $session->region_code,
            $session->country_name ?: $session->country,
        ]);

        return $parts !== [] ? implode(', ', $parts) : 'Ubicacion desconocida';
    }

    public function humanDuration(int $seconds): string
    {
        $seconds = max($seconds, 1);

        if ($seconds < 60) {
            return $seconds . 's';
        }

        $minutes = intdiv($seconds, 60);
        $remainingSeconds = $seconds % 60;

        if ($minutes < 60) {
            return $remainingSeconds > 0 ? "{$minutes}m {$remainingSeconds}s" : "{$minutes}m";
        }

        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        return $remainingMinutes > 0 ? "{$hours}h {$remainingMinutes}m" : "{$hours}h";
    }
}
