<?php

declare(strict_types=1);

namespace App\Jobs\Stripe\Traits;

use App\Models\Enrollment;
use Stripe\Charge;

/**
 * Utility methods for Charge objects
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
trait HandlesCharges
{
    private function findEnrollmentByCharge(Charge $charge): ?Enrollment
    {
        // Check for an invoice
        if (!empty($charge->invoice)) {
            $enrollment = Enrollment::wherePaymentInvoice($charge->invoice)->first();
            logger()->info(
                'Retrieved enrollment {enrollment} using invoice on {charge}.',
                compact('charge', 'enrollment')
            );
        }

        // Check for a payment_intent
        if (!$enrollment && !empty($charge->payment_intent)) {
            $enrollment = Enrollment::wherePaymentIntent($charge->payment_intent)->first();
            logger()->info(
                'Retrieved enrollment {enrollment} using payment_intent on {charge}.',
                compact('charge', 'enrollment')
            );
        }

        // Return enrollment if found
        if ($enrollment) {
            return $enrollment;
        }

        // Log failure to find
        logger()->notice(
            'Cannot process {charge}, since no enrollment was found',
            compact('charge')
        );
        return null;
    }
}
