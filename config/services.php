<?php

$mailtrapAccountId = env('MAILTRAP_ACCOUNT_ID');
$mailtrapEventsEndpoint = env('MAILTRAP_EVENTS_ENDPOINT');

if ($mailtrapEventsEndpoint === '') {
    $mailtrapEventsEndpoint = null;
}

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'mailtrap' => [
        'api_token' => env('MAILTRAP_API_TOKEN'),
        'api_endpoint' => env('MAILTRAP_API_ENDPOINT', 'https://send.api.mailtrap.io/api/send'),
        'api_timeout' => env('MAILTRAP_API_TIMEOUT', 15),
        'account_id' => $mailtrapAccountId,
        'events_endpoint' => $mailtrapEventsEndpoint
            ?: ($mailtrapAccountId ? "https://mailtrap.io/api/accounts/{$mailtrapAccountId}/events" : ''),
        'events_per_page' => env('MAILTRAP_EVENTS_PER_PAGE', 100),
        'events_max_pages' => env('MAILTRAP_EVENTS_MAX_PAGES', 5),
        'send_delay_ms' => env('MAILTRAP_SEND_DELAY_MS', 0),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
