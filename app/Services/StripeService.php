<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\StripeServiceContract;
use App\Services\Traits\HandlesStripeCharges;
use App\Services\Traits\HandlesStripeCoupons;
use App\Services\Traits\HandlesStripeCustomers;
use App\Services\Traits\HandlesStripeInvoices;
use App\Services\Traits\HandlesStripeSources;
use Stripe\Exception\ApiErrorException;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Handles making calls on the Stripe API
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class StripeService implements StripeServiceContract
{
    use HandlesStripeCharges;
    use HandlesStripeCoupons;
    use HandlesStripeCustomers;
    use HandlesStripeInvoices;
    use HandlesStripeSources;

    /**
     * Handles exceptions from Stripe
     *
     * @param ApiErrorException $exception
     * @param int|null $expect Expected error code (like 404)
     * @return void
     * @throws HttpException
     */
    protected function handleError(ApiErrorException $exception, int $expect = null): void
    {
        // Be quiet about expected errors
        if ($expect && $exception->getHttpStatus() === $expect) {
            return;
        }

        // Log error
        logger()->warning('Recieved HTTP error from Stripe API', [
            'exception' => $exception,
            'json' => $exception->getJsonBody()
        ]);

        // Report to Telescope and such
        report($exception);

        if (app()->runningInConsole()) {
            dump($exception);
        }

        // Throw exception
        throw new HttpException(
            500,
            "Er is iets fout gegaan bij het communiceren met onze betaalpartner.",
            $exception
        );
    }
}
