<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Kirimi User Code
    |--------------------------------------------------------------------------
    | Your user code from the Kirimi Dashboard.
    */
    'user_code' => env('KIRIMI_USER_CODE'),

    /*
    |--------------------------------------------------------------------------
    | Kirimi Secret
    |--------------------------------------------------------------------------
    | Your secret key from the Kirimi Dashboard.
    */
    'secret' => env('KIRIMI_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Default Device ID
    |--------------------------------------------------------------------------
    | Optional default device ID used when no device is specified per message.
    */
    'device_id' => env('KIRIMI_DEVICE_ID'),

    /*
    |--------------------------------------------------------------------------
    | API Base URL
    |--------------------------------------------------------------------------
    | The base URL for the Kirimi API.
    */
    'base_url' => env('KIRIMI_BASE_URL', 'https://api.kirimi.id'),

    /*
    |--------------------------------------------------------------------------
    | HTTP Timeout
    |--------------------------------------------------------------------------
    | Request timeout in seconds.
    */
    'timeout' => env('KIRIMI_TIMEOUT', 30),
];
