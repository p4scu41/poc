<?php

namespace RouteRateLimiter\RateLimiter\Middleware;

use Closure;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use RouteRateLimiter\RateLimiter\RateLimiter;

class RateLimit
{
    public function __construct(
        protected RateLimiter $limiter,
    ) {}

    public function handle(Request $request, Closure $next, ?string $limit = null, ?string $identifier = null): mixed
    {
        $limit = $limit !== null ? (int) $limit : $this->resolveLimit($request);
        $identifier = $identifier ?? $this->resolveIdentifier($request);

        $key = $this->buildKey($identifier, $request);

        if ($this->limiter->tooManyAttempts($key, $limit)) {
            $retryAfter = $this->limiter->availableIn($key);

            throw new ThrottleRequestsException(
                'Too Many Attempts.',
                headers: $this->buildHeaders($key, $limit, $retryAfter),
            );
        }

        $this->limiter->hit($key);

        $response = $next($request);

        return $response->withHeaders(
            $this->buildHeaders($key, $limit),
        );
    }

    protected function resolveLimit(Request $request): int
    {
        $path = $request->path();
        $routes = config('ratelimiter.routes', []);

        if (array_key_exists($path, $routes)) {
            return (int) $routes[$path];
        }

        return (int) config('ratelimiter.default', 60);
    }

    protected function resolveIdentifier(Request $request): string
    {
        $strategy = config('ratelimiter.identifier', 'auto');

        if ($strategy === 'user' || ($strategy === 'auto' && $request->user())) {
            return (string) ($request->user()?->getAuthIdentifier() ?? $request->ip());
        }

        return $request->ip() ?? '0.0.0.0';
    }

    protected function buildKey(string $identifier, Request $request): string
    {
        $path = $request->path();
        $minute = (int) (time() / 60);

        return "{$identifier}:{$path}:{$minute}";
    }

    protected function buildHeaders(string $key, int $limit, ?int $retryAfter = null): array
    {
        $headers = [
            'X-RateLimit-Limit' => $limit,
            'X-RateLimit-Remaining' => $this->limiter->remaining($key, $limit),
        ];

        if ($retryAfter !== null) {
            $headers['Retry-After'] = $retryAfter;
            $headers['X-RateLimit-Reset'] = $this->limiter->availableIn($key);
        }

        return $headers;
    }
}
