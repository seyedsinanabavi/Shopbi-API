<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'sitemap.xml', 'robots.txt'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        env('FRONTEND_URL', 'http://localhost:3000'),
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],
    

    'max_age' => 0,

    'supports_credentials' => true,
];