<?php

namespace RouteRateLimiter\RateLimiter;

use Illuminate\Support\ServiceProvider;
use RouteRateLimiter\RateLimiter\Middleware\RateLimit;

class RateLimiterServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/ratelimiter.php',
            'ratelimiter',
        );

        $this->app->singleton('ratelimiter.core', function ($app) {
            return new RateLimiter($app['cache']);
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/ratelimiter.php' => config_path('ratelimiter.php'),
            ], 'ratelimiter-config');
        }

        $this->app->make('router')->aliasMiddleware('rate.limit', RateLimit::class);
    }
}
