<?php

namespace App\Http\Controllers;

use App\Models\AnalyticsEvent;
use App\Models\AnalyticsPageview;
use App\Models\AnalyticsSession;
use App\Services\AnalyticsSessionEnrichmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class AnalyticsController extends Controller
{
    public function collect(Request $request, AnalyticsSessionEnrichmentService $enrichmentService)
    {
        if (!config('analytics.enabled', true)) {
            return response()->noContent();
        }

        $payload = $this->resolvePayload($request);

        if (empty($payload)) {
            return response()->noContent();
        }

        if ($this->shouldIgnore($request, $payload)) {
            return response()->noContent();
        }

        $type = $payload['type'] ?? null;

        if (!in_array($type, ['pageview', 'event', 'heartbeat'], true)) {
            return response()->noContent();
        }

        $sessionId = $payload['session_id'] ?? null;
        $visitorId = $payload['visitor_id'] ?? null;

        if (! $this->isValidUuid($sessionId) || ! $this->isValidUuid($visitorId)) {
            return response()->noContent();
        }

        $url = $this->sanitizeString($payload['url'] ?? null, 2048);
        $path = $this->sanitizePath($payload['path'] ?? null, $url);
        $referrer = $this->sanitizeString($payload['referrer'] ?? null, 2048);
        $referrerDomain = $this->extractDomain($referrer);
        $utm = $this->resolveUtm($payload, $url);
        $language = $this->sanitizeString($payload['language'] ?? $request->getPreferredLanguage(), 10);
        $timezone = $this->sanitizeString($payload['timezone'] ?? null, 50);
        $userAgent = $request->userAgent();
        $deviceType = $this->detectDeviceType($userAgent);
        $browser = $this->detectBrowser($userAgent);
        $os = $this->detectOs($userAgent);
        $rawIp = $request->ip();
        $ipAddress = $this->resolveIp($rawIp);
        $ipHash = $this->hashIp($rawIp);
        $country = $this->sanitizeString($request->header('CF-IPCountry') ?: $payload['country'] ?? null, 2);

        $session = AnalyticsSession::query()->firstOrCreate(
            ['session_id' => $sessionId],
            [
                'visitor_id' => $visitorId,
                'user_id' => auth()->id(),
                'ip_address' => $ipAddress,
                'ip_hash' => $ipHash,
                'user_agent' => $userAgent,
                'device_type' => $deviceType,
                'browser' => $browser,
                'os' => $os,
                'language' => $language,
                'referrer' => $referrer,
                'referrer_domain' => $referrerDomain,
                'landing_url' => $url,
                'landing_path' => $path,
                'utm_source' => $utm['utm_source'] ?? null,
                'utm_medium' => $utm['utm_medium'] ?? null,
                'utm_campaign' => $utm['utm_campaign'] ?? null,
                'utm_term' => $utm['utm_term'] ?? null,
                'utm_content' => $utm['utm_content'] ?? null,
                'country' => $country,
                'last_seen_at' => now(),
            ]
        );

        $session->fill([
            'last_seen_at' => now(),
            'user_id' => $session->user_id ?? auth()->id(),
            'language' => $session->language ?? $language,
            'device_type' => $session->device_type ?? $deviceType,
            'browser' => $session->browser ?? $browser,
            'os' => $session->os ?? $os,
            'ip_address' => $session->ip_address ?? $ipAddress,
            'ip_hash' => $session->ip_hash ?? $ipHash,
            'country' => $session->country ?? $country,
            'referrer' => $session->referrer ?? $referrer,
            'referrer_domain' => $session->referrer_domain ?? $referrerDomain,
        ])->save();

        $session = $enrichmentService->enrich($session, $rawIp, [
            'country' => $country,
            'country_name' => $payload['country_name'] ?? $request->header('CF-IPCountry-Name'),
            'region' => $payload['region'] ?? $request->header('CF-Region'),
            'region_code' => $payload['region_code'] ?? $request->header('CF-Region-Code'),
            'city' => $payload['city'] ?? $request->header('CF-IPCity'),
        ]);

        if ($type === 'heartbeat') {
            return response()->noContent();
        }

        if ($type === 'pageview') {
            AnalyticsPageview::query()->create([
                'analytics_session_id' => $session->id,
                'visitor_id' => $visitorId,
                'user_id' => auth()->id(),
                'url' => $url ?? '',
                'path' => $path,
                'title' => $this->sanitizeString($payload['title'] ?? null, 255),
                'referrer' => $referrer,
                'referrer_domain' => $referrerDomain,
                'utm_source' => $utm['utm_source'] ?? $session->utm_source,
                'utm_medium' => $utm['utm_medium'] ?? $session->utm_medium,
                'utm_campaign' => $utm['utm_campaign'] ?? $session->utm_campaign,
                'utm_term' => $utm['utm_term'] ?? $session->utm_term,
                'utm_content' => $utm['utm_content'] ?? $session->utm_content,
                'viewport_width' => Arr::get($payload, 'viewport.width'),
                'viewport_height' => Arr::get($payload, 'viewport.height'),
                'screen_width' => Arr::get($payload, 'screen.width'),
                'screen_height' => Arr::get($payload, 'screen.height'),
                'timezone' => $timezone,
                'language' => $language,
            ]);

            return response()->noContent();
        }

        $event = is_array($payload['event'] ?? null) ? $payload['event'] : [];
        $element = is_array($event['element'] ?? null) ? $event['element'] : [];
        $pageviewId = AnalyticsPageview::query()
            ->where('analytics_session_id', $session->id)
            ->latest('id')
            ->value('id');

        AnalyticsEvent::query()->create([
            'analytics_session_id' => $session->id,
            'analytics_pageview_id' => $pageviewId,
            'visitor_id' => $visitorId,
            'user_id' => auth()->id(),
            'name' => $this->sanitizeString($event['name'] ?? 'event', 50) ?? 'event',
            'category' => $this->sanitizeString($event['category'] ?? null, 50),
            'label' => $this->sanitizeString($event['label'] ?? null, 255),
            'value' => is_numeric($event['value'] ?? null) ? (int) $event['value'] : null,
            'url' => $url,
            'path' => $path,
            'element_tag' => $this->sanitizeString($element['tag'] ?? null, 50),
            'element_text' => $this->sanitizeString($element['text'] ?? null, 255),
            'element_href' => $this->sanitizeString($element['href'] ?? null, 2048),
            'element_id' => $this->sanitizeString($element['id'] ?? null, 100),
            'element_classes' => $this->sanitizeString($element['classes'] ?? null, 512),
            'element_target' => $this->sanitizeString($element['target'] ?? null, 30),
            'metadata' => $this->sanitizeMetadata($event['metadata'] ?? null),
        ]);

        return response()->noContent();
    }

    protected function resolvePayload(Request $request): array
    {
        $payload = $request->json()->all();

        if (!empty($payload)) {
            return $payload;
        }

        $raw = $request->getContent();
        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    protected function shouldIgnore(Request $request, array $payload): bool
    {
        if (($payload['dnt'] ?? null) || $request->header('DNT') === '1') {
            return true;
        }

        $userAgent = strtolower((string) $request->userAgent());

        if ($this->isBot($userAgent)) {
            return true;
        }

        $path = $this->sanitizePath($payload['path'] ?? null, $payload['url'] ?? null);
        $ignorePaths = (array) config('analytics.ignore_paths', []);

        foreach ($ignorePaths as $ignorePath) {
            if ($ignorePath !== '/' && str_starts_with($path, $ignorePath)) {
                return true;
            }
        }

        $sampleRate = (float) config('analytics.sample_rate', 1);
        $visitorId = (string) ($payload['visitor_id'] ?? '');

        if ($sampleRate < 1 && $visitorId !== '') {
            $hash = abs(crc32($visitorId)) / 0xffffffff;
            if ($hash > $sampleRate) {
                return true;
            }
        }

        if (($payload['type'] ?? null) === 'event' && !config('analytics.track_events', true)) {
            return true;
        }

        if (($payload['type'] ?? null) === 'pageview' && !config('analytics.track_pageviews', true)) {
            return true;
        }

        return false;
    }

    protected function isValidUuid(?string $value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        return (bool) preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $value);
    }

    protected function sanitizeString(?string $value, int $max): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim(preg_replace('/\s+/', ' ', $value));

        if ($value === '') {
            return null;
        }

        return Str::limit($value, $max, '');
    }

    protected function sanitizePath(?string $path, ?string $url): string
    {
        $path = $path ?: parse_url((string) $url, PHP_URL_PATH);
        $path = $path ?: '/';

        return Str::limit($path, 255, '');
    }

    protected function sanitizeMetadata($metadata): ?array
    {
        if (!is_array($metadata)) {
            return null;
        }

        return collect($metadata)
            ->take(20)
            ->map(function ($value) {
                if (is_string($value)) {
                    return Str::limit($value, 255, '');
                }

                if (is_numeric($value) || is_bool($value)) {
                    return $value;
                }

                return null;
            })
            ->filter(fn ($value) => $value !== null)
            ->all();
    }

    protected function resolveUtm(array $payload, ?string $url): array
    {
        $utm = is_array($payload['utm'] ?? null) ? $payload['utm'] : [];
        $query = parse_url((string) $url, PHP_URL_QUERY);
        $params = [];

        if ($query) {
            parse_str($query, $params);
        }

        return [
            'utm_source' => $utm['source'] ?? $params['utm_source'] ?? null,
            'utm_medium' => $utm['medium'] ?? $params['utm_medium'] ?? null,
            'utm_campaign' => $utm['campaign'] ?? $params['utm_campaign'] ?? null,
            'utm_term' => $utm['term'] ?? $params['utm_term'] ?? null,
            'utm_content' => $utm['content'] ?? $params['utm_content'] ?? null,
        ];
    }

    protected function extractDomain(?string $url): ?string
    {
        if (!$url) {
            return null;
        }

        return parse_url($url, PHP_URL_HOST);
    }

    protected function detectDeviceType(?string $userAgent): ?string
    {
        if (!$userAgent) {
            return null;
        }

        $userAgent = strtolower($userAgent);

        if (preg_match('/bot|crawler|spider|slurp|facebookexternalhit/i', $userAgent)) {
            return 'bot';
        }

        if (preg_match('/tablet|ipad/i', $userAgent)) {
            return 'tablet';
        }

        if (preg_match('/mobile|android|iphone|ipod|blackberry|iemobile/i', $userAgent)) {
            return 'mobile';
        }

        return 'desktop';
    }

    protected function detectBrowser(?string $userAgent): ?string
    {
        if (!$userAgent) {
            return null;
        }

        $userAgent = strtolower($userAgent);

        return match (true) {
            str_contains($userAgent, 'edg/') => 'edge',
            str_contains($userAgent, 'opr/') || str_contains($userAgent, 'opera') => 'opera',
            str_contains($userAgent, 'chrome') => 'chrome',
            str_contains($userAgent, 'safari') => 'safari',
            str_contains($userAgent, 'firefox') => 'firefox',
            default => null,
        };
    }

    protected function detectOs(?string $userAgent): ?string
    {
        if (!$userAgent) {
            return null;
        }

        $userAgent = strtolower($userAgent);

        return match (true) {
            str_contains($userAgent, 'windows') => 'windows',
            str_contains($userAgent, 'mac os') || str_contains($userAgent, 'macintosh') => 'macos',
            str_contains($userAgent, 'android') => 'android',
            str_contains($userAgent, 'iphone') || str_contains($userAgent, 'ipad') || str_contains($userAgent, 'ios') => 'ios',
            str_contains($userAgent, 'linux') => 'linux',
            default => null,
        };
    }

    protected function isBot(string $userAgent): bool
    {
        return (bool) preg_match('/bot|crawler|spider|slurp|facebookexternalhit|preview/i', $userAgent);
    }

    protected function resolveIp(?string $ip): ?string
    {
        if (!$ip) {
            return null;
        }

        if (!config('analytics.anonymize_ip', true)) {
            return $ip;
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $parts = explode(':', $ip);
            return implode(':', array_slice($parts, 0, 4)) . '::';
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $parts = explode('.', $ip);
            $parts[3] = '0';
            return implode('.', $parts);
        }

        return $ip;
    }

    protected function hashIp(?string $ip): ?string
    {
        if (!$ip) {
            return null;
        }

        return hash('sha256', $ip . config('app.key'));
    }
}
