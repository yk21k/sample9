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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    // その他の設定
    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'client_id' => env('STRIPE_CLIENT_ID'),
        'redirect_uri' => env('STRIPE_REDIRECT_URI'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    'google' => [

        'youtube' => [

            'client_id' => env(
                'GOOGLE_YOUTUBE_CLIENT_ID'
            ),

            'client_secret' => env(
                'GOOGLE_YOUTUBE_CLIENT_SECRET'
            ),

            'redirect_uri' => env(
                'GOOGLE_YOUTUBE_REDIRECT_URI'
            ),

            'refresh_token' => env(
                'GOOGLE_YOUTUBE_REFRESH_TOKEN'
            ),

        ],
        'cloud_tts' => [
            'project_id' => env(
                'GOOGLE_CLOUD_PROJECT_ID'
            ),
        ],

    ],

    'openai' => [

        'api_key' => env(
            'OPENAI_API_KEY'
        ),

        'narration_model' => env(
            'OPENAI_NARRATION_MODEL',
            'gpt-5-mini'
        ),

    ],

    'google_tts' => [

        'language_code' =>
            env(
                'GOOGLE_TTS_LANGUAGE_CODE',
                'ja-JP'
            ),

        'voice' =>
            env(
                'GOOGLE_TTS_VOICE',
                'ja-JP-Neural2-B'
            ),

        'speaking_rate' =>
            env(
                'GOOGLE_TTS_SPEAKING_RATE',
                1.0
            ),

        'pitch' =>
            env(
                'GOOGLE_TTS_PITCH',
                0.0
            ),

    ],
];
