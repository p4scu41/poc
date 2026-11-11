<?php

namespace RouteRateLimiter\RateLimiter;

use Illuminate\Cache\CacheManager;
use Illuminate\Support\InteractsWithTime;

class RateLimiter
{
    use InteractsWithTime;

    protected CacheManager $cache;

    protected string $prefix = 'rate_limiter:';

    public function __construct(CacheManager $cache)
    {
        $this->cache = $cache;
    }

    public function attempt(string $key, int $maxAttempts, int $decaySeconds = 60): bool
    {
        if ($this->tooManyAttempts($key, $maxAttempts)) {
            return false;
        }

        $this->hit($key, $decaySeconds);

        return true;
    }

    public function tooManyAttempts(string $key, int $maxAttempts): bool
    {
        return $this->current($key) >= $maxAttempts;
    }

    public function hit(string $key, int $decaySeconds = 60): int
    {
        $store = $this->cache->store($this->store());

        $key = $this->cleanKey($key);

        $current = (int) $store->get($key);

        if ($current === 0) {
            $store->add($key, 1, $decaySeconds);

            return 1;
        }

        $store->increment($key);

        return $current + 1;
    }

    public function current(string $key): int
    {
        return (int) $this->cache->store($this->store())->get($this->cleanKey($key));
    }

    public function remaining(string $key, int $maxAttempts): int
    {
        return max(0, $maxAttempts - $this->current($key));
    }

    public function reset(string $key): void
    {
        $this->cache->store($this->store())->forget($this->cleanKey($key));
    }

    public function cleanKey(string $key): string
    {
        return $this->prefix.$key;
    }

    public function availableIn(string $key): int
    {
        $store = $this->cache->store($this->store());

        if (method_exists($store, 'ttl')) {
            $ttl = $store->ttl($this->cleanKey($key));

            return max(0, $ttl);
        }

        return 0;
    }

    protected function store(): ?string
    {
        return config('ratelimiter.store');
    }
}
