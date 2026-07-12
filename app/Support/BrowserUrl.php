<?php

namespace App\Support;

final class BrowserUrl
{
    public static function normalize(?string $url): ?string
    {
        if (! filled($url) || ! app()->bound('request')) {
            return $url;
        }

        $request = request();
        $requestHost = $request->getHost();
        $path = parse_url($url, PHP_URL_PATH) ?: '';

        if (! self::isLocalHost($requestHost)
            || (! str_starts_with($path, '/storage/') && ! str_starts_with($path, '/images/'))) {
            return $url;
        }

        $query = parse_url($url, PHP_URL_QUERY);

        return $request->getSchemeAndHttpHost().$path.($query ? '?'.$query : '');
    }

    private static function isLocalHost(string $host): bool
    {
        return $host === 'localhost' || $host === '127.0.0.1' || str_ends_with($host, '.test');
    }
}
