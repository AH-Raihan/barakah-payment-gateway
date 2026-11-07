<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Barakah Wallet Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL of the Barakah Institutional Wallet server.
    | Example: "http://127.0.0.1:8000" or "https://barakah.institute.edu"
    |
    */
    'base_url' => env('BARAKAH_BASE_URL', 'http://127.0.0.1:8000'),

    /*
    |--------------------------------------------------------------------------
    | Barakah Project API Key
    |--------------------------------------------------------------------------
    |
    | Generated in your Barakah Admin Portal -> Payment Gateway API -> Create Client.
    |
    */
    'api_key' => env('BARAKAH_API_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | HTTP Request Timeout (seconds)
    |--------------------------------------------------------------------------
    */
    'timeout' => (int) env('BARAKAH_TIMEOUT', 15),

    /*
    |--------------------------------------------------------------------------
    | Gateway Mode
    |--------------------------------------------------------------------------
    |
    | Supported: "sandbox", "live"
    |
    */
    'mode' => env('BARAKAH_MODE', 'sandbox'),
];

