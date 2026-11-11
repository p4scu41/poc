# Route Rate Limiter

A configurable per-route rate limiting package for Laravel.

## Installation

### 1. Add to `composer.json`

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "packages/ratelimiter"
        }
    ],
    "require": {
        "route-rate-limiter/rate-limiter": "*"
    }
}
```

Then run:

```bash
composer update
```

### 2. Register Service Provider

The service provider is auto-discovered by Laravel. No manual registration is needed.

### 3. Publish Configuration (Optional)

```bash
php artisan vendor:publish --tag=ratelimiter-config
```

## Configuration

Publish the config file or configure directly in `config/ratelimiter.php`:

```php
return [
    'default' => 60,
    'routes' => [
        'api/posts' => 30,
        'api/auth/login' => 5,
    ],
    'identifier' => 'auto',
    'store' => null,
];
```

### Options

| Option | Default | Description |
|--------|---------|-------------|
| `default` | `60` | Default max requests per minute for all routes |
| `routes` | `[]` | Per-route limit overrides (key = request path) |
| `identifier` | `'auto'` | Client identification strategy: `ip`, `user`, or `auto` |
| `store` | `null` | Cache store to use (`null` = default store) |

## Usage

Apply the `rate.limit` middleware to routes or groups:

```php
use Illuminate\Support\Facades\Route;

// Uses default or per-route config limit
Route::get('/posts', function () {
    return response()->json(['message' => 'ok']);
})->middleware('rate.limit');

// Override limit via middleware parameter (10 req/min)
Route::get('/search', function () {
    return response()->json(['message' => 'ok']);
})->middleware('rate.limit:10');

// Apply to a group
Route::middleware('rate.limit')->group(function () {
    Route::get('/users', ...);
    Route::get('/settings', ...);
});
```

When a client exceeds the limit, a `429 Too Many Requests` response is returned with `Retry-After`, `X-RateLimit-Limit`, and `X-RateLimit-Remaining` headers.

### Using the Facade

```php
use RouteRateLimiter\RateLimiter\Facades\RateLimiter;

RateLimiter::attempt('login:user:1', 5, 60);
RateLimiter::remaining('login:user:1', 5);
RateLimiter::reset('login:user:1');
```

## Testing

```bash
php artisan test --group=ratelimiter
```
