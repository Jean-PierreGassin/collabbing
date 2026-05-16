<?php

use App\Models\User;

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'github' => [
        'client_id' => env('GITHUB_CLIENT_ID'),
        'client_secret' => env('GITHUB_CLIENT_SECRET'),
        'redirect' => env('APP_URL').'/auth/github/callback',
        'repository_sync' => [
            'max_per_run' => env('GITHUB_REPOSITORY_SYNC_MAX_PER_RUN', 25),
            'next_min_minutes' => env('GITHUB_REPOSITORY_SYNC_NEXT_MIN_MINUTES', 360),
            'next_max_minutes' => env('GITHUB_REPOSITORY_SYNC_NEXT_MAX_MINUTES', 720),
            'retry_min_minutes' => env('GITHUB_REPOSITORY_SYNC_RETRY_MIN_MINUTES', 120),
            'retry_max_minutes' => env('GITHUB_REPOSITORY_SYNC_RETRY_MAX_MINUTES', 240),
        ],
    ],

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],

    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => 'us-east-1',
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],

];
