<?php

return [

    'name' => env('APP_NAME', 'سایت من'),

    'env' => env('APP_ENV', 'production'),

    'debug' => (bool) env('APP_DEBUG', false),

    'url' => env('APP_URL', 'http://localhost'),

    // ⭐ URL فرانت‌اند (برای sitemap و CORS)
    'frontend_url' => env('FRONTEND_URL', 'http://localhost:3000'),

    // ⭐ منطقه زمانی ایران
    'timezone' => env('APP_TIMEZONE', 'Asia/Tehran'),

    // ⭐ زبان پیش‌فرض فارسی
    'locale' => env('APP_LOCALE', 'fa'),

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    'faker_locale' => env('APP_FAKER_LOCALE', 'fa_IR'),

    'cipher' => 'AES-256-CBC',

    'key' => env('APP_KEY'),

    'previous_keys' => [
        ...array_filter(
            explode(',', (string) env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store'  => env('APP_MAINTENANCE_STORE', 'database'),
    ],

];