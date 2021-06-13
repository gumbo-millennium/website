<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\StripeServiceContract;
use App\Services\Traits\HandlesStripeCharges;
use App\Services\Traits\HandlesStripeCoupons;
use App\Services\Traits\HandlesStripeCustomers;
use App\Services\Traits\HandlesStripeInvoices;
use App\Services\Traits\HandlesStripeRefunds;
use App\Services\Traits\HandlesStripeSources;
use Stripe\Exception\ApiErrorException;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Handles making calls on the Stripe API.
 */
class StripeService implements StripeServiceContract
{
    use HandlesStripeCharges;
    use HandlesStripeCoupons;
    use HandlesStripeCustomers;
    use HandlesStripeInvoices;
    use HandlesStripeRefunds;
    use HandlesStripeSources;

    /**
     * Handles exceptions from Stripe.
     *
     * @param null|int $expect Expected error code (like 404)
     * @throws HttpException
     */
    protected function handleError(ApiErrorException $exception, ?int $expect = null): void
    {
        // Be quiet about expected errors
        if ($expect && $exception->getHttpStatus() === $expect) {
            return;
        }

        // Log error
        logger()->warning('Recieved HTTP error from Stripe API', [
            'exception' => $exception,
            'json' => $exception->getJsonBody(),
        ]);

        // Report to Telescope and such
        report($exception);

        // Throw exception
        throw new HttpException(
            500,
            'Er is iets fout gegaan bij het communiceren met onze betaalpartner.',
            $exception
        );
    }
}
