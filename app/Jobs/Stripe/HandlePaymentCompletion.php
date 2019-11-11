<?php

namespace App\Jobs\Stripe;

use Stripe\Event as StripeEvent;

/**
 * Handles completed payments (iDeal sends them async)
 *
 * Called on payment_intent.succeeded
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class HandlePaymentIntentCompletion extends StripeJob
{
    /**
     * Execute the job.
     *
     * @return void
     */
    public function process(StripeEvent $event): void
    {
        logger()->debug('HELP, I FOUND A {object}', [
            'event' => $event,
            'object' => $event->object
        ]);
    }
}
