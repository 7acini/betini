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

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'fipe' => [
        'base_url' => env('FIPE_API_BASE_URL', 'https://parallelum.com.br/fipe/api/v1'),
    ],

    'google_business_profile' => [
        'client_id' => env('GOOGLE_BUSINESS_PROFILE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_BUSINESS_PROFILE_CLIENT_SECRET'),
        'refresh_token' => env('GOOGLE_BUSINESS_PROFILE_REFRESH_TOKEN'),
        'account_id' => env('GOOGLE_BUSINESS_PROFILE_ACCOUNT_ID'),
        'location_id' => env('GOOGLE_BUSINESS_PROFILE_LOCATION_ID'),
        'reviews_limit' => (int) env('GOOGLE_BUSINESS_PROFILE_REVIEWS_LIMIT', 10),
        'base_url' => env('GOOGLE_BUSINESS_PROFILE_BASE_URL', 'https://mybusiness.googleapis.com/v4'),
        'token_url' => env('GOOGLE_OAUTH_TOKEN_URL', 'https://oauth2.googleapis.com/token'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
