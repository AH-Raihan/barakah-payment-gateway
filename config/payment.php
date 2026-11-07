<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Payment Gateway Environment
    |--------------------------------------------------------------------------
    |
    | Supported: "sandbox", "live"
    | In sandbox mode, OTP is displayed on screen and in JSON responses
    | to allow instantaneous testing by developers without telecom delay.
    |
    */
    'mode' => env('PAYMENT_GATEWAY_MODE', 'sandbox'),

    /*
    |--------------------------------------------------------------------------
    | Payment OTP Lifetime (minutes)
    |--------------------------------------------------------------------------
    */
    'otp_expiry_minutes' => (int) env('PAYMENT_OTP_EXPIRY', 5),
];

