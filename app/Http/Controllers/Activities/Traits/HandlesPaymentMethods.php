<?php

declare(strict_types=1);

namespace App\Http\Controllers\Activities\Traits;

use App\Models\Enrollment;
use App\Service\StripeErrorService;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;

/**
 * HAndles creating iDEAL payment methods at Stripe
 */
trait HandlesPaymentMethods
{
    use FormatsStripeData;

    /**
     * Returns the payment method for this intent, which matches the
     * bank requested.
     *
     * @param PaymentIntent $intent Intent to update
     * @param string $bank Bank name
     * @return PaymentMethod|null
     */
    protected function getIdealPaymentMethod(PaymentIntent $intent, string $bank): ?PaymentMethod
    {
        // Check if a payment method is present
        if ($intent->payment_method !== null) {
            try {
                // Retrieve the method
                $method = PaymentMethod::retrieve($intent->payment_method);

                // Check if the method matches the currently requested bank
                if ($method->ideal['bank'] === $bank) {
                    return $method;
                }
            } catch (ApiErrorException $e) {
                app(StripeErrorService::class)->handleUpdate($e);
            }
        }

        try {
            // Create a new method
            return PaymentMethod::create([
                'type' => 'ideal',
                'ideal' => [
                    'bank' => $bank
                ]
            ]);
        } catch (ApiErrorException $e) {
            // Handle error
            app(StripeErrorService::class)->handleCreate($e);
        }
    }
}
