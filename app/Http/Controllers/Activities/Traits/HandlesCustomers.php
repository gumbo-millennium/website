<?php

declare(strict_types=1);

namespace App\Http\Controllers\Activities\Traits;

use App\Jobs\Stripe\CustomerUpdateJob;
use App\Models\Enrollment;
use App\Services\StripeErrorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use LogicException;
use RuntimeException;
use Stripe\Exception\ApiErrorException;
use Stripe\Invoice;
use Stripe\Mandate;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use Stripe\Source;

/**
 * Handles customers in Stripe
 */
trait HandlesCustomers
{
    use FormatsStripeData;

    /**
     * Makes sure a Stripe customer is present for this user. Reloads the $enrollment if required
     *
     * @param Enrollment $enrollment
     */
    protected function ensureCustomerExists(Enrollment &$enrollment): void
    {
        // Skip if a user exists
        if ($enrollment->user->stripe_customer_id) {
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
