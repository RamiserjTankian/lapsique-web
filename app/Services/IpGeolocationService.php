<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class IpGeolocationService
{
    public function lookup(?string $ip): array
    {
        if (! config('analytics.ip_lookup.enabled', true)) {
            return [];
        }

        if (! $this->isLookupCandidate($ip)) {
            return [];
        }

        $cacheKey = 'analytics:geo:v2:' . sha1((string) $ip);

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey, []);
        }

        $result = $this->fetch((string) $ip);

        if ($result !== []) {
            $cacheTtlHours = max((int) config('analytics.ip_lookup.cache_hours', 168), 1);
            Cache::put($cacheKey, $result, now()->addHours($cacheTtlHours));
        }

        return $result;
    }

    protected function fetch(string $ip): array
    {
        foreach ($this->providers($ip) as $provider) {
            try {
                $response = Http::acceptJson()
                    ->timeout(max((int) config('analytics.ip_lookup.timeout', 4), 1))
                    ->get($provider['url']);

                if (! $response->successful()) {
                    continue;
                }

                $payload = $response->json();

                if (! is_array($payload) || (($payload['error'] ?? false) || ($payload['success'] ?? true) === false)) {
                    continue;
                }

                $result = ($provider['map'])($payload);

                if ($result !== []) {
                    return $result;
                }
            } catch (\Throwable $exception) {
                Log::warning('Analytics IP lookup provider failed', [
                    'ip' => $ip,
                    'provider' => $provider['name'],
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return [];
    }

    protected function providers(string $ip): array
    {
        $primaryEndpoint = rtrim((string) config('analytics.ip_lookup.endpoint', 'https://ipapi.co'), '/');

        return array_filter([
            $primaryEndpoint !== '' ? [
                'name' => 'ipapi',
                'url' => "{$primaryEndpoint}/{$ip}/json/",
                'map' => fn (array $payload): array => $this->normalize([
                    'country' => $payload['country_code'] ?? null,
                    'country_name' => $payload['country_name'] ?? $payload['country_name_full'] ?? null,
                    'region' => $payload['region'] ?? null,
                    'region_code' => $payload['region_code'] ?? null,
                    'city' => $payload['city'] ?? null,
                ]),
            ] : null,
            [
                'name' => 'ipwhois',
                'url' => "https://ipwho.is/{$ip}",
                'map' => fn (array $payload): array => $this->normalize([
                    'country' => $payload['country_code'] ?? null,
                    'country_name' => $payload['country'] ?? null,
                    'region' => $payload['region'] ?? null,
                    'region_code' => $payload['region_code'] ?? null,
                    'city' => $payload['city'] ?? null,
                ]),
            ],
            [
                'name' => 'ipinfo',
                'url' => "https://ipinfo.io/{$ip}/json",
                'map' => fn (array $payload): array => $this->normalize([
                    'country' => $payload['country'] ?? null,
                    'region' => $payload['region'] ?? null,
                    'city' => $payload['city'] ?? null,
                ]),
            ],
        ]);
    }

    protected function normalize(array $payload): array
    {
        $countryCode = $this->limit($payload['country'] ?? null, 2);

        return array_filter([
            'country' => $countryCode,
            'country_name' => $this->limit(
                $payload['country_name'] ?? $this->countryNameFromCode($countryCode),
                120
            ),
            'region' => $this->limit($payload['region'] ?? null, 100),
            'region_code' => $this->limit($payload['region_code'] ?? null, 40),
            'city' => $this->limit($payload['city'] ?? null, 100),
        ], static fn ($value) => $value !== null && $value !== '');
    }

    protected function countryNameFromCode(?string $countryCode): ?string
    {
        if (! $countryCode || ! class_exists(\Locale::class)) {
            return null;
        }

        $name = \Locale::getDisplayRegion('-' . strtoupper($countryCode), 'es');

        return is_string($name) && trim($name) !== '' ? $name : null;
    }

    protected function isLookupCandidate(?string $ip): bool
    {
        if (! is_string($ip) || trim($ip) === '') {
            return false;
        }

        $ip = trim($ip);

        if (in_array(Str::lower($ip), ['127.0.0.1', '::1', 'localhost'], true)) {
            return false;
        }

        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) !== false;
    }

    protected function limit(?string $value, int $length): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        if ($value === '') {
            return null;
        }

        return Str::limit($value, $length, '');
    }
}
