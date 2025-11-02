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

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-5-mini'),
        'temperature' => (float) env('OPENAI_TEMPERATURE', 0.3),
        'max_tokens' => (int) env('OPENAI_MAX_TOKENS', 4000), // Increased for GPT-5 reasoning tokens
    ],

    'huggingface' => [
        'api_key' => env('HUGGINGFACE_API_KEY'),
        'model' => env('HUGGINGFACE_MODEL', 'mistralai/Mixtral-8x7B-Instruct-v0.1'),
        'temperature' => (float) env('HUGGINGFACE_TEMPERATURE', 0.7),
        'max_tokens' => (int) env('HUGGINGFACE_MAX_TOKENS', 1400),
    ],

    'ai' => [
        'default_provider' => env('AI_PROVIDER', 'openai'), // openai or huggingface
    ],

];
