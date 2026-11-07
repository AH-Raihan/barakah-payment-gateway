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
        'api_key' => env('FIREBASE_API_KEY', 'AIzaSyD6jpgKOUzP6mDXtFVOZNh7KMHIemc8Kc8'),
        'auth_domain' => env('FIREBASE_AUTH_DOMAIN', 'barakah-c44d4.firebaseapp.com'),
        'project_id' => env('FIREBASE_PROJECT_ID', 'barakah-c44d4'),
        'storage_bucket' => env('FIREBASE_STORAGE_BUCKET', 'barakah-c44d4.firebasestorage.app'),
        'messaging_sender_id' => env('FIREBASE_MESSAGING_SENDER_ID', '86263442374'),
        'app_id' => env('FIREBASE_APP_ID', '1:86263442374:web:bb0228b229b477cb4ca0c7'),
        'measurement_id' => env('FIREBASE_MEASUREMENT_ID', 'G-CNFXMCXTY7'),
        'vapid_key' => env('FIREBASE_VAPID_KEY', ''),
    ],
];

