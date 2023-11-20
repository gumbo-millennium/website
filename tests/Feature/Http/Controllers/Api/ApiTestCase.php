<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

abstract class ApiTestCase extends TestCase
{
    /**
     * Disable a rate limit so the tests can run without being throttled.
     */
    public function disableRateLimit(string $name = 'api'): void
    {
        RateLimiter::for($name, fn () => Limit::none());
        RateLimiter::clear($name);
    }

    /**
     * Disable the rate limit on the api route group for all tests.
     * @before
     */
    public function disableRateLimitOnSetUp(): void
    {
        $this->afterApplicationCreated(function () {
            $this->disableRateLimit();
        });
    }
}
