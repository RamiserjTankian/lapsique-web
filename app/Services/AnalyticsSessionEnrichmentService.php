<?php

namespace App\Services;

use App\Models\AnalyticsSession;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

class AnalyticsSessionEnrichmentService
{
    protected ?array $sessionColumns = null;

    public function __construct(
        protected IpGeolocationService $ipGeolocationService
    ) {
    }

    public function enrich(AnalyticsSession $session, ?string $lookupIp = null, array $context = []): AnalyticsSession
    {
        $updates = [];

        $source = $this->resolveSource(
            $session->utm_source,
            $session->utm_medium,
            $session->referrer_domain,
            $session->landing_url
        );

        if (! $session->source_type && ($source['source_type'] ?? null)) {
            $updates['source_type'] = $source['source_type'];
        }

        if (! $session->source_label && ($source['source_label'] ?? null)) {
            $updates['source_label'] = $source['source_label'];
        }

        $geoContext = [
            'country' => $this->sanitize(Arr::get($context, 'country'), 2),
            'country_name' => $this->sanitize(Arr::get($context, 'country_name'), 120),
            'region' => $this->sanitize(Arr::get($context, 'region'), 100),
            'region_code' => $this->sanitize(Arr::get($context, 'region_code'), 40),
            'city' => $this->sanitize(Arr::get($context, 'city'), 100),
        ];

        $geoLookup = $this->ipGeolocationService->lookup($lookupIp ?: $session->ip_address);
        $geo = array_merge(
            $geoLookup,
            array_filter($geoContext, static fn ($value) => filled($value))
        );

        foreach (['country', 'country_name', 'region', 'region_code', 'city'] as $field) {
            if (! $session->{$field} && ($geo[$field] ?? null)) {
                $updates[$field] = $geo[$field];
            }
        }

        $updates = $this->filterExistingColumns($updates);

        if ($updates !== []) {
            $session->forceFill($updates)->save();
        }

        return $session->refresh();
    }

    public function resolveSource(?string $utmSource, ?string $utmMedium, ?string $referrerDomain, ?string $landingUrl = null): array
    {
        $utmSource = $this->sanitize($utmSource, 255);
        $utmMedium = $this->sanitize($utmMedium, 255);
        $referrerDomain = $this->sanitize($referrerDomain, 255);
        $host = $landingUrl ? parse_url($landingUrl, PHP_URL_HOST) : null;

        if ($utmSource) {
            return [
                'source_type' => $this->classifySource($utmSource, $utmMedium),
                'source_label' => $utmSource,
            ];
        }

        if ($referrerDomain && $host && Str::lower($referrerDomain) === Str::lower($host)) {
            return [
                'source_type' => 'internal',
                'source_label' => $referrerDomain,
            ];
        }

        if ($referrerDomain) {
            return [
                'source_type' => $this->classifySource($referrerDomain, $utmMedium),
                'source_label' => $referrerDomain,
            ];
        }

        return [
            'source_type' => 'direct',
            'source_label' => 'direct',
        ];
    }

    protected function classifySource(?string $source, ?string $medium): string
    {
        $haystack = Str::lower(trim((string) $source) . ' ' . trim((string) $medium));

        if ($haystack === '') {
            return 'direct';
        }

        if (preg_match('/instagram|facebook|tiktok|youtube|x\.com|twitter|linkedin|threads|whatsapp|telegram|pinterest|snapchat|ig\b|fb\b/', $haystack)) {
            return 'social';
        }

        if (preg_match('/google|bing|duckduckgo|yahoo|search|organic/', $haystack)) {
            return 'search';
        }

        if (preg_match('/email|mailchimp|mailtrap|newsletter|smtp/', $haystack)) {
            return 'email';
        }

        if (preg_match('/cpc|ppc|ads|paid|display|remarketing|retargeting/', $haystack)) {
            return 'paid';
        }

        if (preg_match('/affiliate|partner|referral|referrer/', $haystack)) {
            return 'referral';
        }

        return 'referral';
    }

    protected function sanitize(?string $value, int $length): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim(preg_replace('/\s+/', ' ', $value));

        if ($value === '') {
            return null;
        }

        return Str::limit($value, $length, '');
    }

    protected function filterExistingColumns(array $updates): array
    {
        $columns = $this->getSessionColumns();

        return array_filter(
            $updates,
            static fn (mixed $value, string $column): bool => in_array($column, $columns, true),
            ARRAY_FILTER_USE_BOTH
        );
    }

    protected function getSessionColumns(): array
    {
        if ($this->sessionColumns !== null) {
            return $this->sessionColumns;
        }

        return $this->sessionColumns = Schema::getColumnListing((new AnalyticsSession())->getTable());
    }
}
