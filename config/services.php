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

    'qmark_pdf' => [
    'embed' => env('QMARK_PDF_EMBED_URL', env('IMAGEWM_SERVICE_ENDPOINT').'/embed'),
    'extract' => env('QMARK_PDF_EXTRACT_URL', env('IMAGEWM_SERVICE_ENDPOINT').'/extract'),
    ],

    'qmark_image' => [
        'embed' => env('QMARK_IMAGE_EMBED_URL', env('IMAGEWM_SERVICE_ENDPOINT').'/embed'),
        'extract' => env('QMARK_IMAGE_EXTRACT_URL', env('IMAGEWM_SERVICE_ENDPOINT').'/extract'),
    ],

    'qmark_video' => [
        'embed' => env('QMARK_VIDEO_EMBED_URL', 'http://localhost:5003/embed'),
        'extract' => env('QMARK_VIDEO_EXTRACT_URL', 'http://localhost:5003/extract'),
    ],

    'imagewm' => [
        'endpoint' => env('IMAGEWM_SERVICE_ENDPOINT'),
    ],

    'pqc' => [
        'endpoint' => env('PQC_SERVICE_ENDPOINT'),
    ],

];
