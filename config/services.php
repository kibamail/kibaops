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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
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

    'github' => [
        'app_id' => env('KIBAOPS_GITHUB_APP_ID'),
        'app_name' => env('KIBAOPS_GITHUB_APP_NAME'),
        'app_secret' => env('KIBAOPS_GITHUB_APP_SECRET'),
        'private_key' => env('KIBAOPS_GITHUB_APP_PRIVATE_KEY'),
        'webhook_secret' => env('KIBAOPS_GITHUB_APP_WEBHOOKS_SECRET'),
        'callback_url' => env('KIBAOPS_GITHUB_CALLBACK_URL'),
        'webhook_url' => env('NGROK_URL', env('APP_URL')) . '/workspaces/connections/github/webhooks',
    ],

];
