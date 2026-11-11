<?php

namespace RouteRateLimiter\RateLimiter\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool attempt(string $key, int $maxAttempts, int $decaySeconds = 60)
 * @method static bool tooManyAttempts(string $key, int $maxAttempts)
 * @method static int hit(string $key, int $decaySeconds = 60)
 * @method static int current(string $key)
 * @method static int remaining(string $key, int $maxAttempts)
 * @method static void reset(string $key)
 * @method static int availableIn(string $key)
 *
 * @see \RouteRateLimiter\RateLimiter\RateLimiter
 */
class RateLimiter extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'ratelimiter.core';
    }
}
