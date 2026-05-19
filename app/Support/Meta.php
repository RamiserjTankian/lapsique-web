<?php

namespace App\Support;

use App\Models\SiteSetting;

class Meta
{
    public static function pixelId(): ?string
    {
        $fromSettings = SiteSetting::metaPixelId();
        $fromEnv = config('meta.pixel.id');

        $id = $fromSettings ?: $fromEnv;

        return filled($id) ? (string) $id : null;
    }

    public static function pixelEnabled(): bool
    {
        return (bool) config('meta.pixel.enabled') && filled(static::pixelId());
    }

    public static function capiEnabled(): bool
    {
        return (bool) config('meta.capi.enabled')
            && filled(static::pixelId())
            && filled(config('meta.marketing_api.access_token'));
    }

    /**
     * @return array{enabled: bool, id: ?string, autoTrack: bool, trackPageView: bool}
     */
    public static function pixelClientConfig(): array
    {
        return [
            'enabled' => static::pixelEnabled(),
            'id' => static::pixelId(),
            'autoTrack' => (bool) config('meta.pixel.auto_track', true),
            'trackPageView' => (bool) config('meta.pixel.track_pageview', true),
        ];
    }
}
