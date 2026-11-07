<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default SMS Driver
    |--------------------------------------------------------------------------
    |
    | Supported: "log", "http", "mock"
    |
    */
    'default' => env('SMS_DRIVER', 'log'),

    'drivers' => [
        'log' => [
            'channel' => env('SMS_LOG_CHANNEL', 'single'),
        ],

        /*
        | Generic HTTP / Bangladesh SMS Gateway (e.g., Greenweb, SSL Wireless, etc.)
        */
        'http' => [
            'endpoint' => env('SMS_GATEWAY_URL', 'https://api.sms-gateway.test/send'),
            'api_key' => env('SMS_GATEWAY_KEY', ''),
            'sender_id' => env('SMS_SENDER_ID', 'BARAKAH'),
        ],
    ],

    /*
    | Enable or disable SMS alerts globally
    */
    'enabled' => env('SMS_NOTIFICATIONS_ENABLED', true),
];

