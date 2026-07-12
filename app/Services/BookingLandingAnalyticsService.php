<?php

namespace App\Services;

use App\Models\AnalyticsSession;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class BookingLandingAnalyticsService
{
    public const LANDING_PATH = '/';

    public const LANDING_PATHS = [
        '/',
        '/reels-de-comida',
        '/dj-set',
        '/sesiones-de-dron',
        '/avances-de-obra',
        '/portafolio',
        '/trabajos-en-video',
    ];

    public static function trackedEventNames(): array
    {
        return [
            'booking_page_viewed',
            'food_reels_page_viewed',
            'djset_page_viewed',
            'drone_session_page_viewed',
            'construction_progress_page_viewed',
            'video_play',
            'video_progress',
            'video_complete',
            'form_started',
            'click',
            'submit',
            'section_view',
            'scroll_depth',
            'engaged',
            'page_exit',
            'hero_cta_clicked',
            'header_cta_clicked',
            'sticky_cta_clicked',
            'booking_cta_clicked',
            'deliverables_viewed',
            'process_viewed',
            'proof_section_viewed',
            'equipment_viewed',
            'gear_section_viewed',
            'workflow_section_viewed',
            'package_includes_viewed',
            'faq_opened',
            'reel_card_clicked',
            'reel_player_opened',
            'reel_watch_milestone',
            'reel_overlay_cta_clicked',
            'reel_player_agendar_clicked',
            'booking_widget_viewed',
            'booking_popup_shown',
            'booking_popup_dismissed',
            'booking_popup_cta_clicked',
            'booking_popup_whatsapp_clicked',
            'booking_calendar_opened',
            'booking_date_selected',
            'booking_slot_selected',
            'booking_slot_cleared',
            'booking_form_viewed',
            'booking_form_started',
            'booking_abandoned',
            'booking_checkout_started',
            'booking_form_submitted',
            'booking_payment_cta_clicked',
            'booking_test_confirmed',
            'booking_confirmed',
            'booking_payment_pending',
            'booking_payment_failed',
        ];
    }

    public static function stageDefinitions(): array
    {
        return [
            ['key' => 'landing_view', 'label' => 'Visita landing', 'color' => 'gray', 'events' => []],
            ['key' => 'reel_engaged', 'label' => 'Video interactuado', 'color' => 'info', 'events' => ['reel_card_clicked', 'reel_player_opened', 'video_play']],
            ['key' => 'reel_watch', 'label' => 'Reel >25% visto', 'color' => 'info', 'events' => ['reel_watch_milestone']],
            ['key' => 'reel_overlay_cta', 'label' => 'CTA overlay reel', 'color' => 'info', 'events' => ['reel_overlay_cta_clicked']],
            ['key' => 'reel_modal_cta', 'label' => 'Agendar desde modal', 'color' => 'primary', 'events' => ['reel_player_agendar_clicked']],
            ['key' => 'popup_shown', 'label' => 'Popup visto', 'color' => 'info', 'events' => ['booking_popup_shown']],
            ['key' => 'popup_clicked', 'label' => 'CTA popup', 'color' => 'info', 'events' => ['booking_popup_cta_clicked']],
            ['key' => 'calendar_opened', 'label' => 'Calendario abierto', 'color' => 'primary', 'events' => ['booking_calendar_opened']],
            ['key' => 'date_selected', 'label' => 'Fecha elegida', 'color' => 'warning', 'events' => ['booking_date_selected']],
            ['key' => 'slot_selected', 'label' => 'Horario elegido', 'color' => 'warning', 'events' => ['booking_slot_selected']],
            ['key' => 'form_started', 'label' => 'Registro iniciado', 'color' => 'success', 'events' => ['booking_form_started']],
            ['key' => 'checkout_started', 'label' => 'Checkout iniciado', 'color' => 'success', 'events' => ['booking_checkout_started', 'booking_form_viewed']],
            ['key' => 'form_submitted', 'label' => 'Formulario enviado', 'color' => 'success', 'events' => ['booking_form_submitted', 'booking_payment_cta_clicked']],
            ['key' => 'pending', 'label' => 'Pago en revisión', 'color' => 'warning', 'events' => ['booking_payment_pending']],
            ['key' => 'failed', 'label' => 'Pago fallido', 'color' => 'danger', 'events' => ['booking_payment_failed']],
            ['key' => 'confirmed', 'label' => 'Reserva confirmada', 'color' => 'success', 'events' => ['booking_confirmed']],
        ];
    }

    public function baseQuery(): Builder
    {
        $paths = self::LANDING_PATHS;

        return AnalyticsSession::query()
            ->where(function (Builder $query) use ($paths): void {
                $query
                    ->whereIn('landing_path', $paths)
                    ->orWhereHas('pageviews', function (Builder $pageviews) use ($paths): void {
                        $pageviews->whereIn('path', $paths);
                    });
            })
            ->withCount([
                'pageviews as booking_pageviews_count' => function (Builder $query) use ($paths): void {
                    $query->whereIn('path', $paths);
                },
                'events as booking_events_count' => function (Builder $query): void {
                    $query->whereIn('name', self::trackedEventNames());
                },
                'events as video_plays_count' => fn (Builder $query) => $query->where('name', 'video_play'),
                'events as contact_events_count' => fn (Builder $query) => $query
                    ->where(function (Builder $events): void {
                        $events->whereIn('name', [
                            'food_reels_whatsapp_cta_clicked',
                            'djset_whatsapp_cta_clicked',
                            'drone_session_whatsapp_cta_clicked',
                            'construction_progress_whatsapp_cta_clicked',
                            'service_landing_whatsapp_clicked',
                            'whatsapp_popup_clicked',
                        ])->orWhere(function (Builder $clicks): void {
                            $clicks->where('name', 'click')->where('element_href', 'like', 'https://wa.me/%');
                        });
                    }),
                'events as form_starts_count' => fn (Builder $query) => $query
                    ->whereIn('name', ['form_started', 'booking_form_started']),
            ])
            ->with([
                'pageviews' => function ($query) use ($paths): void {
                    $query->whereIn('path', $paths)->orderBy('created_at');
                },
                'events' => function ($query): void {
                    $query->whereIn('name', self::trackedEventNames())->orderBy('created_at');
                },
            ]);
    }

    public function snapshotForQuery(Builder $query): array
    {
        $sessions = (clone $query)->get();
        $uniqueVisitors = $sessions->pluck('visitor_id')->filter()->unique()->count();

        $stageCounts = collect(self::stageDefinitions())
            ->map(function (array $stage) use ($sessions) {
                $count = $sessions->filter(function (AnalyticsSession $session) use ($stage): bool {
                    $summary = $this->sessionStageSummary($session);

                    return $summary['rank'] >= $summary['stage_ranks'][$stage['key']];
                })->count();

                return array_merge($stage, ['sessions' => $count]);
            })
            ->values();

        $convertedSessions = $sessions->filter(fn (AnalyticsSession $session) => $this->sessionStageSummary($session)['key'] === 'confirmed')->count();
        $pendingSessions = $sessions->filter(fn (AnalyticsSession $session) => $this->sessionStageSummary($session)['key'] === 'pending')->count();
        $failedSessions = $sessions->filter(fn (AnalyticsSession $session) => $this->sessionStageSummary($session)['key'] === 'failed')->count();
        $popupShownSessions = $sessions->filter(fn (AnalyticsSession $session) => $this->hasEvent($session, ['booking_popup_shown']))->count();
        $popupCtaSessions = $sessions->filter(fn (AnalyticsSession $session) => $this->hasEvent($session, ['booking_popup_cta_clicked']))->count();
        $reelEngagedSessions = $sessions->filter(fn (AnalyticsSession $session) => $this->hasEvent($session, ['reel_card_clicked', 'reel_player_opened']))->count();
        $reelWatchSessions = $sessions->filter(fn (AnalyticsSession $session) => $this->hasEvent($session, ['reel_watch_milestone']))->count();
        $dateSelectedSessions = $sessions->filter(fn (AnalyticsSession $session) => $this->hasEvent($session, ['booking_date_selected']))->count();
        $formSubmittedSessions = $sessions->filter(fn (AnalyticsSession $session) => $this->hasEvent($session, ['booking_form_submitted']))->count();
        $videoPlaySessions = $sessions->filter(fn (AnalyticsSession $session) => $this->hasEvent($session, ['video_play', 'reel_player_opened']))->count();
        $videoCompleteSessions = $sessions->filter(fn (AnalyticsSession $session) => $this->hasEvent($session, ['video_complete']))->count();
        $contactSessions = $sessions->filter(fn (AnalyticsSession $session) => $this->hasEvent($session, [
            'food_reels_whatsapp_cta_clicked', 'djset_whatsapp_cta_clicked', 'drone_session_whatsapp_cta_clicked',
            'construction_progress_whatsapp_cta_clicked', 'service_landing_whatsapp_clicked', 'whatsapp_popup_clicked',
        ]))->count();

        $sources = $sessions
            ->groupBy(fn (AnalyticsSession $session) => $this->sourceLabel($session))
            ->map(function (Collection $items, string $source) {
                $confirmed = $items->filter(fn (AnalyticsSession $session) => $this->sessionStageSummary($session)['key'] === 'confirmed')->count();

                return [
                    'source' => $source,
                    'channel' => $this->channelLabel($items->first()),
                    'sessions' => $items->count(),
                    'confirmed' => $confirmed,
                    'conversion_rate' => $items->count() > 0 ? round(($confirmed / $items->count()) * 100, 2) : 0,
                ];
            })
            ->sortByDesc('sessions')
            ->take(8)
            ->values();

        $dropoffs = $sessions
            ->map(function (AnalyticsSession $session) {
                $summary = $this->sessionStageSummary($session);

                return [
                    'stage' => $summary['label'],
                    'key' => $summary['key'],
                    'rank' => $summary['rank'],
                ];
            })
            ->reject(fn (array $row) => $row['key'] === 'confirmed')
            ->groupBy('stage')
            ->map(fn (Collection $items, string $stage) => [
                'stage' => $stage,
                'sessions' => $items->count(),
            ])
            ->sortByDesc('sessions')
            ->values()
            ->take(8);

        $recentSessions = $sessions
            ->sortByDesc('created_at')
            ->take(12)
            ->map(function (AnalyticsSession $session) {
                $summary = $this->sessionStageSummary($session);

                return [
                    'session' => $session,
                    'stage' => $summary['label'],
                    'stage_color' => $summary['color'],
                    'source' => $this->sourceLabel($session),
                    'duration_seconds' => $this->durationSeconds($session),
                ];
            })
            ->values();

        return [
            'stats' => [
                'sessions' => $sessions->count(),
                'unique_visitors' => $uniqueVisitors,
                'reel_engaged' => $reelEngagedSessions,
                'reel_watch' => $reelWatchSessions,
                'popup_shown' => $popupShownSessions,
                'popup_cta' => $popupCtaSessions,
                'date_selected' => $dateSelectedSessions,
                'submitted' => $formSubmittedSessions,
                'video_play' => $videoPlaySessions,
                'video_complete' => $videoCompleteSessions,
                'contact' => $contactSessions,
                'confirmed' => $convertedSessions,
                'pending' => $pendingSessions,
                'failed' => $failedSessions,
                'popup_rate' => $sessions->count() > 0 ? round(($popupShownSessions / $sessions->count()) * 100, 2) : 0,
                'popup_click_rate' => $popupShownSessions > 0 ? round(($popupCtaSessions / $popupShownSessions) * 100, 2) : 0,
                'conversion_rate' => $sessions->count() > 0 ? round(($convertedSessions / $sessions->count()) * 100, 2) : 0,
            ],
            'funnel' => $stageCounts->all(),
            'sources' => $sources->all(),
            'dropoffs' => $dropoffs->all(),
            'recent_sessions' => $recentSessions->all(),
        ];
    }

    public function sessionStageSummary(AnalyticsSession $session): array
    {
        $stageRanks = collect(self::stageDefinitions())
            ->values()
            ->mapWithKeys(fn (array $stage, int $index) => [$stage['key'] => $index + 1])
            ->all();

        $current = [
            'key' => 'landing_view',
            'label' => 'Visita landing',
            'color' => 'gray',
            'rank' => $stageRanks['landing_view'],
            'stage_ranks' => $stageRanks,
            'reached_at' => $session->created_at,
        ];

        foreach (self::stageDefinitions() as $stage) {
            if ($stage['key'] === 'landing_view') {
                continue;
            }

            $event = $this->firstMatchingEvent($session, $stage['events']);

            if (! $event) {
                continue;
            }

            $current = [
                'key' => $stage['key'],
                'label' => $stage['label'],
                'color' => $stage['color'],
                'rank' => $stageRanks[$stage['key']],
                'stage_ranks' => $stageRanks,
                'reached_at' => $event->created_at,
            ];
        }

        return $current;
    }

    public function sourceLabel(AnalyticsSession $session): string
    {
        if ($session->utm_source) {
            return $session->utm_source;
        }

        if ($session->referrer_domain) {
            return $session->referrer_domain;
        }

        return 'directo';
    }

    public function channelLabel(?AnalyticsSession $session): string
    {
        if (! $session) {
            return 'Directo';
        }

        $medium = Str::lower((string) $session->utm_medium);
        $source = Str::lower((string) $session->utm_source);
        $referrer = Str::lower((string) $session->referrer_domain);

        return match (true) {
            str_contains($medium, 'paid') || str_contains($medium, 'cpc') || str_contains($medium, 'ads') => 'Pago',
            str_contains($medium, 'email') => 'Email',
            str_contains($medium, 'whatsapp') || str_contains($source, 'whatsapp') || str_contains($referrer, 'whatsapp') => 'WhatsApp',
            str_contains($medium, 'social') || str_contains($source, 'instagram') || str_contains($source, 'facebook') || str_contains($referrer, 'instagram') || str_contains($referrer, 'facebook') => 'Social',
            str_contains($referrer, 'google') || str_contains($medium, 'organic') => 'Orgánico',
            $session->referrer_domain => 'Referido',
            default => 'Directo',
        };
    }

    public function durationSeconds(AnalyticsSession $session): int
    {
        $start = $session->created_at ?: now();
        $end = $session->last_seen_at ?: $start;

        return max($start->diffInSeconds($end), 0);
    }

    protected function hasEvent(AnalyticsSession $session, array $eventNames): bool
    {
        return $this->firstMatchingEvent($session, $eventNames) !== null;
    }

    protected function firstMatchingEvent(AnalyticsSession $session, array $eventNames): ?object
    {
        $events = $session->relationLoaded('events')
            ? $session->events
            : $session->events()->whereIn('name', $eventNames)->orderBy('created_at')->get();

        return $events
            ->first(fn ($event) => in_array($event->name, $eventNames, true));
    }
}
