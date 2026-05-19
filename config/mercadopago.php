<?php

return [
    'client_id' => env('MERCADOPAGO_CLIENT_ID'),
    'client_secret' => env('MERCADOPAGO_CLIENT_SECRET'),
    'access_token' => env('MERCADOPAGO_ACCESS_TOKEN'),
    'public_key' => env('MERCADOPAGO_PUBLIC_KEY'),
    'webhook_secret' => env('MERCADOPAGO_WEBHOOK_SECRET'),
    'statement_descriptor' => env('MERCADOPAGO_STATEMENT_DESCRIPTOR', 'LAPSIQUE'),
    'currency' => env('MERCADOPAGO_CURRENCY', 'MXN'),
    'sandbox' => env('MERCADOPAGO_SANDBOX', false),
    'redirect_uri' => env('MERCADOPAGO_REDIRECT_URI'),
    'api_base_url' => env('MERCADOPAGO_API_BASE_URL', 'https://api.mercadopago.com'),
    'oauth_authorize_url' => env('MERCADOPAGO_OAUTH_AUTHORIZE_URL', 'https://auth.mercadopago.com/authorization'),
    'oauth_token_url' => env('MERCADOPAGO_OAUTH_TOKEN_URL', 'https://api.mercadopago.com/oauth/token'),
];
