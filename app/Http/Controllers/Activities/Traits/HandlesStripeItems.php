<?php

declare(strict_types=1);

namespace App\Http\Controllers\Activities\Traits;

/**
 * Loads all traits required for working with the Stripe API.
 */
trait HandlesStripeItems
{
    use FormatsStripeData;
    use HandlesCustomers;
    use HandlesInvoices;
    use HandlesPaymentIntents;
}
