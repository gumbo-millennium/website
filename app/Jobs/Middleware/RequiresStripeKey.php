<?php

namespace App\Jobs\Middleware;

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
        if (empty(Stripe::getApiKey())) {
            $job->fail('No API key set for Stripe');
        }

        $next($job);
    }
}
