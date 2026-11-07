<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Firebase Cloud Messaging (FCM) Server Configuration
    |--------------------------------------------------------------------------
    |
    | Used by the backend to push real-time transaction notifications.
    | Supports Server Key (FCM Legacy) or HTTP v1 Service Account credentials.
    |
    */
    'server_key' => env('FCM_SERVER_KEY', ''),
    'project_id' => env('FIREBASE_PROJECT_ID', 'barakah-wallet'),
    'credentials_file' => env('FIREBASE_CREDENTIALS', null),

    /*
    |--------------------------------------------------------------------------
    | Firebase Web Push Client Configuration (Passed to JavaScript Frontend)
    |--------------------------------------------------------------------------
    */
    'web' => [
        'api_key' => env('FIREBASE_API_KEY', ''),
        'auth_domain' => env('FIREBASE_AUTH_DOMAIN', 'barakah-wallet.firebaseapp.com'),
        'project_id' => env('FIREBASE_PROJECT_ID', 'barakah-wallet'),
        'storage_bucket' => env('FIREBASE_STORAGE_BUCKET', 'barakah-wallet.appspot.com'),
        'messaging_sender_id' => env('FIREBASE_MESSAGING_SENDER_ID', '1234567890'),
        'app_id' => env('FIREBASE_APP_ID', ''),
        'vapid_key' => env('FIREBASE_VAPID_KEY', ''),
    ],
];

