<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Cross-Origin Resource Sharing (CORS) Configuration
|--------------------------------------------------------------------------
|
| Controls which origins may call the API from a browser. The allowed origins
| are environment-driven (CORS_ALLOWED_ORIGINS, comma-separated) so that local,
| staging and production hosts are configured without code changes.
|
| `supports_credentials` is true because the SPA authenticates with Sanctum via
| session cookies, which the browser only sends on credentialed requests.
|
*/

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('CORS_ALLOWED_ORIGINS', 'http://localhost:5173')),
    ))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
