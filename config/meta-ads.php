<?php

return [
    'enabled' => config('meta.marketing_api.enabled'),

    'app_id' => config('meta.marketing_api.app_id'),
    'app_secret' => config('meta.marketing_api.app_secret'),
    'access_token' => config('meta.marketing_api.access_token'),
    'ad_account_id' => config('meta.marketing_api.ad_account_id'),

    'api_version' => config('meta.marketing_api.api_version'),

    'pixel_id' => config('meta.pixel.id'),

    'capi_enabled' => config('meta.capi.enabled'),

    'test_event_code' => config('meta.capi.test_event_code'),

    'sync_days_default' => config('meta.attribution.sync_days_default'),

    'report_cache_minutes' => config('meta.attribution.report_cache_minutes'),

    'utm_template' => config('meta.attribution.utm_template'),
];
