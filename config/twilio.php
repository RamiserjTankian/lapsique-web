<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Twilio Account SID
    |--------------------------------------------------------------------------
    |
    | Your Twilio Account SID from https://www.twilio.com/console
    |
    */
    'account_sid' => env('TWILIO_ACCOUNT_SID'),

    /*
    |--------------------------------------------------------------------------
    | Twilio Auth Token
    |--------------------------------------------------------------------------
    |
    | Your Twilio Auth Token from https://www.twilio.com/console
    |
    */
    'auth_token' => env('TWILIO_AUTH_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Twilio Phone Numbers
    |--------------------------------------------------------------------------
    |
    | Your Twilio phone numbers for different services
    |
    */
    'from' => [
        'sms' => env('TWILIO_SMS_FROM'),
        'whatsapp' => env('TWILIO_WHATSAPP_FROM', 'whatsapp:+14155238886'),
        'voice' => env('TWILIO_VOICE_FROM'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Twilio Verify Service SID
    |--------------------------------------------------------------------------
    |
    | Your Twilio Verify service SID for phone verification
    |
    */
    'verify_sid' => env('TWILIO_VERIFY_SID'),

    /*
    |--------------------------------------------------------------------------
    | Webhook URLs
    |--------------------------------------------------------------------------
    |
    | URLs where Twilio will send status callbacks
    |
    */
    'webhooks' => [
        'sms_status' => env('APP_URL') . '/webhooks/twilio/sms/status',
        'whatsapp_status' => env('APP_URL') . '/webhooks/twilio/whatsapp/status',
        'voice_status' => env('APP_URL') . '/webhooks/twilio/voice/status',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Settings
    |--------------------------------------------------------------------------
    */
    'defaults' => [
        'max_price' => env('TWILIO_MAX_PRICE', 0.05), // Maximum price per message
        'validity_period' => env('TWILIO_VALIDITY_PERIOD', 14400), // 4 hours
    ],
];

