<?php

namespace App\Jobs\Stripe;

use App\Jobs\Middleware\RequiresStripeKey;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * Basic Stripe job, with a webhook
 */
abstract class StripeJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Makes sure a Stripe key is present
     * @return array
     */
    public function middleware()
    {
        return [new RequiresStripeKey()];
    }
}
