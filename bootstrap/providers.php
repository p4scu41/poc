<?php

use App\Providers\AppServiceProvider;
use App\Providers\FortifyServiceProvider;
use RouteRateLimiter\RateLimiter\RateLimiterServiceProvider;

return [
    AppServiceProvider::class,
    FortifyServiceProvider::class,
    RateLimiterServiceProvider::class,
];
