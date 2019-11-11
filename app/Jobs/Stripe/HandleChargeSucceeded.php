<?php

namespace App\Jobs\Stripe;

use App\Models\Enrollment;
use App\Models\States\Enrollment\Cancelled;
use App\Models\States\Enrollment\Confirmed;
use App\Models\States\Enrollment\Paid;
use App\Models\States\Enrollment\Seeded;
use Stripe\Charge;
use Stripe\Event as StripeEvent;

/**
 * Handles completed charges.
 *
 * Called on payment_intent.succeeded
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class HandleChargeSucceeded extends StripeJob
{
    /**
     * Execute the job.
     *
     * @return void
     */
    public function process(StripeEvent $event): void
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

        // We can only transition from seeded and confirmed
        if ($enrollment->state->isOneOf([Seeded::class, Confirmed::class])) {
            return;
        }

        // Transition to a paid state.
        $enrollment->state->transitionTo(Paid::class);
        $enrollment->save();
    }
}
