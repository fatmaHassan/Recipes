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

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'themealdb' => [
        'base_url' => env('THEMEALDB_BASE_URL', 'https://www.themealdb.com/api/json/v1/1/'),
        'api_key' => env('THEMEALDB_API_KEY', '1'),
        'pool_size' => env('THEMEALDB_POOL_SIZE', 10),
        'batch_sleep_ms' => env('THEMEALDB_BATCH_SLEEP_MS', 0),
        // Download recipe images into storage during sync (permission granted
        // by TheMealDB). Off = images keep being hotlinked from their CDN;
        // failed downloads always fall back to hotlinking as well.
        'mirror_images' => env('THEMEALDB_MIRROR_IMAGES', false),
        // Disk for mirrored images. "public" needs `php artisan storage:link`;
        // an S3-compatible disk (e.g. Supabase Storage) is recommended on
        // Render, whose filesystem is wiped on every deploy.
        'image_disk' => env('THEMEALDB_IMAGE_DISK', 'public'),
        'image_pool_size' => env('THEMEALDB_IMAGE_POOL_SIZE', 10),
    ],

    'mealdb_sync' => [
        // Empty token disables the /api/sync/mealdb endpoint (always 403).
        'token' => env('MEALDB_SYNC_TOKEN'),
    ],

];
