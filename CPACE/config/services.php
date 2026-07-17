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

    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('APP_URL') . '/auth/google/callback',
    ],

    'gemini' => [
        'key'   => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-flash-latest'),
    ],

    'openrouter' => [
        'key'    => env('OPENROUTER_API_KEY'),
        // Tried in order until one answers - free models share upstream
        // capacity with everyone, so any single one can be busy at times.
        'models' => explode(',', env('OPENROUTER_MODELS',
            'nvidia/nemotron-3-super-120b-a12b:free'
            . ',qwen/qwen3-next-80b-a3b-instruct:free'
            . ',meta-llama/llama-3.3-70b-instruct:free'
            . ',openai/gpt-oss-20b:free'
        )),
    ],

    'azure' => [
        'client_id'     => env('MICROSOFT_CLIENT_ID'),
        'client_secret' => env('MICROSOFT_CLIENT_SECRET'),
        'redirect'      => env('APP_URL') . '/auth/microsoft/callback',
        'tenant'        => env('MICROSOFT_TENANT_ID', 'common'),
    ],

];
