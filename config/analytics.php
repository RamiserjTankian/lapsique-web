<?php

return [
    'enabled' => env('ANALYTICS_ENABLED', true),
    'track_pageviews' => env('ANALYTICS_TRACK_PAGEVIEWS', true),
    'track_events' => env('ANALYTICS_TRACK_EVENTS', true),
    'track_clicks' => env('ANALYTICS_TRACK_CLICKS', true),
    'track_forms' => env('ANALYTICS_TRACK_FORMS', true),
    'track_engagement' => env('ANALYTICS_TRACK_ENGAGEMENT', true),
    'sample_rate' => env('ANALYTICS_SAMPLE_RATE', 1),
    'session_timeout' => env('ANALYTICS_SESSION_TIMEOUT', 30),
    'dashboard_days' => env('ANALYTICS_DASHBOARD_DAYS', 30),
    'anonymize_ip' => env('ANALYTICS_ANONYMIZE_IP', true),
    'reporting_timezone' => env('ANALYTICS_REPORTING_TIMEZONE', 'America/Merida'),
    'presence' => [
        'heartbeat_interval_seconds' => env('ANALYTICS_PRESENCE_HEARTBEAT_INTERVAL_SECONDS', 15),
        'active_window_seconds' => env('ANALYTICS_PRESENCE_ACTIVE_WINDOW_SECONDS', 45),
        'recent_window_minutes' => env('ANALYTICS_PRESENCE_RECENT_WINDOW_MINUTES', 15),
        'dashboard_polling_interval' => env('ANALYTICS_PRESENCE_DASHBOARD_POLLING_INTERVAL', '10s'),
    ],
    'ip_lookup' => [
        'enabled' => env('ANALYTICS_IP_LOOKUP_ENABLED', true),
        'endpoint' => env('ANALYTICS_IP_LOOKUP_ENDPOINT', 'https://ipapi.co'),
        'timeout' => env('ANALYTICS_IP_LOOKUP_TIMEOUT', 4),
        'cache_hours' => env('ANALYTICS_IP_LOOKUP_CACHE_HOURS', 168),
    ],
    'ignore_paths' => [
        '/admin',
        '/analytics/collect',
        '/up',
    ],
];
