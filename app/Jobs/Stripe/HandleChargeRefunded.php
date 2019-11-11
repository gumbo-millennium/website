<?php

namespace App\Jobs\Stripe;

use App\Models\Enrollment;
use App\Models\States\Enrollment\Cancelled;
use Stripe\Charge;
use Stripe\Event;

/**
 * Handles refunds. Usually they're already handled, but
 * there is a chance that the refund was issued via the
 * admin panel.
 *
 * Called on payment_intent.succeeded
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class HandleChargeRefunded extends StripeJob
{
    /**
     * Execute the job.
     *
     * @param Event $event
     * @return void
     */
    public function process(Event $event): void
    {
        // Get object
        $charge = Charge::constructFrom($event->object);

        // Skip if no charge is present
        if (empty($charge->payment_intent)) {
            return;
        }

        // Check if the payment intent exists
        $enrollment = Enrollment::wherePaymentIntent($charge->payment_intent)->first();

        // Skip if not found
        if ($enrollment === null) {
            return;
        }

        // If the enrollment is already cancelled, don't do anything
        if ($enrollment->state->is(Cancelled::class)) {
            return;
        }

        // Cancel the event
        $enrollment->state->transitionTo(Cancelled::class);
        $enrollment->save();
    }
}
