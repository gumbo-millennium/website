<?php

declare(strict_types=1);

namespace App\Jobs\Middleware;

use RuntimeException;
use Stripe\Stripe;

class RequiresStripeKey
{
    /**
     * Process the queued job.
     *
     * @param  mixed  $job
     * @param  callable  $next
     * @return mixed
     */
    public function handle($job, $next)
    {
        // Fail if key is missing
        if (empty(Stripe::getApiKey())) {
            $job->fail(new RuntimeException('No API key set for Stripe'));
            return;
        }

        // Start job
        $next($job);
    }
}
