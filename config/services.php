<?php

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

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        // 'redirect' => 'http://izf.duckdns.org/demo-v7/login/google/callback',
    ],

    'instagram' => [
        'client_id' => null,
        'client_secret' => null,
        'redirect' => null
    ],

    'twitter' => [
        'client_id' => null,
        'client_secret' => null,
        'redirect' => null
    ],

    'facebook' => [
        'client_id' => null,
        'client_secret' => null,
        'redirect' => null
    ],

    'github' => [
        'client_id' => null,
        'client_secret' => null,
        'redirect' => null
    ],

];
