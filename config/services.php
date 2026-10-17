<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel'              => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | SMS Services (ایران)
    |--------------------------------------------------------------------------
    |
    | پشتیبانی از Kavenegar و SMS.ir
    |
    */

    'sms' => [
        'driver' => env('SMS_DRIVER', 'log'),

        'kavenegar' => [
            'api_key'  => env('KAVENEGAR_API_KEY'),
            'sender'   => env('KAVENEGAR_SENDER'),
            'template' => env('KAVENEGAR_TEMPLATE', 'verify'),
        ],

        'smsir' => [
            'api_key'     => env('SMSIR_API_KEY'),
            'template_id' => env('SMSIR_TEMPLATE_ID'),
            'line_number' => env('SMSIR_LINE_NUMBER'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | OTP Configuration
    |--------------------------------------------------------------------------
    */

    'otp' => [
        'expire_minutes' => env('OTP_EXPIRE_MINUTES', 2),
        'length'         => env('OTP_LENGTH', 5),
        'max_attempts'   => env('OTP_MAX_ATTEMPTS', 5),
        'resend_delay'   => env('OTP_RESEND_DELAY', 60),
    ],

];