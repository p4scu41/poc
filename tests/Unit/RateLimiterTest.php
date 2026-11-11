<?php

use RouteRateLimiter\RateLimiter\RateLimiter;
use Tests\TestCase;

uses(TestCase::class)->group('ratelimiter');

beforeEach(function () {
    app('cache')->store('array')->flush();
    $this->limiter = new RateLimiter(app('cache'));
});

it('hits and increments the counter', function () {
    $count = $this->limiter->hit('test:key');

    expect($count)->toBe(1)
        ->and($this->limiter->current('test:key'))->toBe(1);
});

it('increments the counter on subsequent hits', function () {
    $this->limiter->hit('test:key');
    $count = $this->limiter->hit('test:key');

    expect($count)->toBe(2);
});

it('detects too many attempts', function () {
    $this->limiter->hit('test:key');
    $this->limiter->hit('test:key');

    expect($this->limiter->tooManyAttempts('test:key', 2))->toBeTrue();
});

it('allows attempts under the limit', function () {
    $this->limiter->hit('test:key');

    expect($this->limiter->tooManyAttempts('test:key', 5))->toBeFalse();
});

it('returns remaining attempts correctly', function () {
    $this->limiter->hit('test:key');

    expect($this->limiter->remaining('test:key', 5))->toBe(4);
});

it('returns zero remaining when limit is exceeded', function () {
    $this->limiter->hit('test:key');
    $this->limiter->hit('test:key');
    $this->limiter->hit('test:key');

    expect($this->limiter->remaining('test:key', 3))->toBe(0);
});

it('resets the counter for a given key', function () {
    $this->limiter->hit('test:key');

    $this->limiter->reset('test:key');

    expect($this->limiter->current('test:key'))->toBe(0);
});

it('attempt returns true when under limit', function () {
    $result = $this->limiter->attempt('test:key', 5);

    expect($result)->toBeTrue()
        ->and($this->limiter->current('test:key'))->toBe(1);
});

it('attempt returns false when over limit', function () {
    $this->limiter->hit('test:key');
    $this->limiter->hit('test:key');
    $this->limiter->hit('test:key');
    $this->limiter->hit('test:key');
    $this->limiter->hit('test:key');

    $result = $this->limiter->attempt('test:key', 5);

    expect($result)->toBeFalse();
});

it('uses separate counters for different keys', function () {
    $this->limiter->hit('key:a');
    $this->limiter->hit('key:a');
    $this->limiter->hit('key:b');

    expect($this->limiter->current('key:a'))->toBe(2)
        ->and($this->limiter->current('key:b'))->toBe(1);
});

it('returns available in seconds for a key', function () {
    $this->limiter->hit('test:key', 60);

    $availableIn = $this->limiter->availableIn('test:key');

    expect($availableIn)->toBeGreaterThanOrEqual(0);
});
