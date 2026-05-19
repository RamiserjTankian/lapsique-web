<?php

/**
 * Configuración centralizada de Meta (Pixel, CAPI, Marketing API, atribución).
 * Los archivos meta-pixel.php y meta-ads.php reexportan claves para compatibilidad.
 */
return [
    'pixel' => [
        'enabled' => env('META_PIXEL_ENABLED', false),
        'id' => env('META_PIXEL_ID'),
        'auto_track' => env('META_PIXEL_AUTO_TRACK', true),
        'track_pageview' => env('META_PIXEL_TRACK_PAGEVIEW', true),
    ],

    'capi' => [
        'enabled' => env('META_CAPI_ENABLED', false),
        'test_event_code' => env('META_TEST_EVENT_CODE'),
    ],

    'marketing_api' => [
        'enabled' => env('META_ADS_ENABLED', false),
        'app_id' => env('META_APP_ID'),
        'app_secret' => env('META_APP_SECRET'),
        'access_token' => env('META_ACCESS_TOKEN'),
        'ad_account_id' => env('META_AD_ACCOUNT_ID'),
        'api_version' => env('META_API_VERSION', 'v21.0'),
    ],

    'attribution' => [
        'sync_days_default' => (int) env('META_SYNC_DAYS_DEFAULT', 30),
        'report_cache_minutes' => (int) env('META_REPORT_CACHE_MINUTES', 30),
        'utm_template' => '?utm_source=facebook&utm_medium=paid&utm_campaign={{campaign.id}}&utm_content={{ad.id}}&utm_term={{adset.name}}',
    ],
];
