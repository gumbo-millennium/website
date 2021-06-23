<?php

declare(strict_types=1);

namespace App\Http\Controllers\Activities\Traits;

use App\Jobs\Stripe\CustomerUpdateJob;
use App\Models\Enrollment;
use App\Services\StripeErrorService;
use Stripe\Exception\ApiErrorException;

/**
 * Handles customers in Stripe.
 */
trait HandlesCustomers
{
    /**
     * Makes sure a Stripe customer is present for this user. Reloads the $enrollment if required.
     */
    protected function ensureCustomerExists(Enrollment &$enrollment): void
    {
        // Skip if a user exists
        if ($enrollment->user->stripe_id) {
            return;
        }

        try {
            CustomerUpdateJob::dispatchNow($enrollment->user);
            $enrollment->refresh();
        } catch (ApiErrorException $error) {
            app(StripeErrorService::class)->handleCreate($error);
        }
    }
}
