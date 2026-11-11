<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Rate Limit
    |--------------------------------------------------------------------------
    |
    | This value defines the maximum number of requests allowed per minute
    | for any route that does not have a specific limit defined below.
    |
    */

    'default' => env('RATE_LIMITER_DEFAULT', 60),

    /*
    |--------------------------------------------------------------------------
    | Per-Route Rate Limits
    |--------------------------------------------------------------------------
    |
    | Here you may define specific rate limits for individual routes.
    | The key should match the request path (without leading slash).
    |
    | Example:
    |     'api/posts' => 30,
    |     'api/auth/login' => 5,
    |
    */

    'routes' => [],

    /*
    |--------------------------------------------------------------------------
    | Identifier Strategy
    |--------------------------------------------------------------------------
    |
    | This setting controls how the package identifies unique clients.
    |
    | Supported: 'ip', 'user', 'auto'
    |
    | - 'ip':   Always use the client IP address.
    | - 'user': Always use the authenticated user ID. Falls back to IP
    |           for unauthenticated requests.
    | - 'auto': Use the authenticated user ID if available, otherwise IP.
    |
    */

    'identifier' => env('RATE_LIMITER_IDENTIFIER', 'auto'),

    /*
    |--------------------------------------------------------------------------
    | Cache Store
    |--------------------------------------------------------------------------
    |
    | The cache store to use for rate limit counters. Set to null to use
    | your application's default cache store.
    |
    */

    'store' => env('RATE_LIMITER_CACHE_STORE'),

];
