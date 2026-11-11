<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

uses()->group('ratelimiter', 'ratelimiter-middleware');

beforeEach(function () {
    Cache::store('array')->flush();

    Config::set('ratelimiter.default', 5);
    Config::set('ratelimiter.routes', [
        'api/posts' => 2,
    ]);
    Config::set('ratelimiter.identifier', 'ip');
    Config::set('ratelimiter.store', 'array');

    Route::middleware('rate.limit')->get('api/posts', function () {
        return response()->json(['message' => 'ok']);
    })->name('api.posts');

    Route::middleware('rate.limit')->get('api/default', function () {
        return response()->json(['message' => 'default']);
    })->name('api.default');

    Route::middleware('rate.limit:1')->get('api/limited', function () {
        return response()->json(['message' => 'limited']);
    })->name('api.limited');
});

it('allows requests under the default limit', function () {
    for ($i = 0; $i < 5; $i++) {
        $response = $this->getJson('api/default');
        $response->assertOk();
    }
});

it('blocks requests exceeding the default limit', function () {
    for ($i = 0; $i < 5; $i++) {
        $this->getJson('api/default');
    }

    $response = $this->getJson('api/default');

    $response->assertStatus(429);
});

it('applies per-route config limits', function () {
    $this->getJson('api/posts')->assertOk();
    $this->getJson('api/posts')->assertOk();

    $response = $this->getJson('api/posts');

    $response->assertStatus(429);
});

it('respects middleware parameter overrides', function () {
    $this->getJson('api/limited')->assertOk();

    $response = $this->getJson('api/limited');

    $response->assertStatus(429);
});

it('includes X-RateLimit headers on successful responses', function () {
    $response = $this->getJson('api/default');

    $response->assertHeader('X-RateLimit-Limit', '5')
        ->assertHeader('X-RateLimit-Remaining');
});

it('includes Retry-After header on 429 responses', function () {
    for ($i = 0; $i < 5; $i++) {
        $this->getJson('api/default');
    }

    $response = $this->getJson('api/default');

    $response->assertStatus(429)
        ->assertHeader('Retry-After')
        ->assertHeader('X-RateLimit-Reset');
});

it('returns correct remaining count', function () {
    $response = $this->getJson('api/default');
    $response->assertHeader('X-RateLimit-Remaining', '4');

    $response = $this->getJson('api/default');
    $response->assertHeader('X-RateLimit-Remaining', '3');
});

it('resets the counter after clearing cache', function () {
    for ($i = 0; $i < 5; $i++) {
        $this->getJson('api/default');
    }

    $this->getJson('api/default')->assertStatus(429);

    app('cache')->store('array')->flush();

    $this->getJson('api/default')->assertOk();
});
