<?php

declare(strict_types=1);

namespace App\Http\Controllers\Activities\Traits;

use App\Models\Enrollment;
use App\Services\StripeErrorService;
use Illuminate\Http\RedirectResponse;
use InvalidArgumentException;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\Source;

/**
 * Creates Payment Intents on Stripe
 */
trait HandlesPaymentIntents
{
    /**
     * Builds a redirect to Stripe, if applicable. Returns null otherwise.
     * @param PaymentIntent $intent
     * @return RedirectResponse|null
     */
    public function redirectPaymentIntent(PaymentIntent $intent): ?RedirectResponse
    {
        // Check the status
        if ($intent->status !== PaymentIntent::STATUS_REQUIRES_ACTION) {
            return null;
        }

        // Check the action
        if (!$intent->next_action) {
            return null;
        }

        // Check action type and url
        $actionType = data_get($intent->next_action, 'type');
        $actionUrl = data_get($intent->next_action, 'redirect_to_url.url');
        if ($actionType !== 'redirect_to_url' || empty($actionUrl)) {
            return null;
        }

        // Redirect to Stripe
        return redirect()->away($actionUrl);
    }

    /**
     * Creates a Payment Intent at Stripe and returns it
     * Returns null if $enrollment is a free activity (for this user)
     * @param Enrollment $enrollment
     * @return PaymentIntent|null
     */
    protected function createPaymentIntent(Enrollment $enrollment): ?PaymentIntent
    {
        // Return null if price is empty
        if (empty($enrollment->price)) {
            return null;
        }

        // Make sure we have a user
        $this->ensureCustomerExists($enrollment);

        // Build info
        $sharedInfo = $this->getEnrollmentInformation($enrollment);
        $intentInfo = [
            'payment_method_types' => ['ideal'],
        ];

        if ($enrollment->user->stripe_id) {
            $intentInfo['customer'] = $enrollment->user->stripe_id;
        }

        // Create Intent on the Stripe servers
        try {
            return PaymentIntent::create(array_merge($sharedInfo, $intentInfo));
        } catch (ApiErrorException $error) {
            app(StripeErrorService::class)->handleCreate($error);
        }
    }
    /**
     * Creates a Payment Intent at Stripe, returns the ID.
     * Returns null if $enrollment is a free activity (for this user)
     * @param Enrollment $enrollment
     * @return string|null
     */
    protected function getPaymentIntent(Enrollment $enrollment): ?PaymentIntent
    {
        // Return null if price is empty
        if (empty($enrollment->price)) {
            return null;
        }

        // Create the intent if one is not yet present
        if ($enrollment->payment_intent === null) {
            return $this->createPaymentIntent($enrollment);
        }

        // Retrieve intent from server
        $intent = null;
        try {
            // Retrieve intent
            $intent = PaymentIntent::retrieve($enrollment->payment_intent);
        } catch (ApiErrorException $error) {
            app(StripeErrorService::class)->handleUpdate($error);
            return null;
        }

        // If the intent was cancelled, we create a new one
        if ($intent->status === PaymentIntent::STATUS_CANCELED) {
            return $this->createPaymentIntent($enrollment);
        }

        // Intent is ok
        return $intent;
    }

    /**
     * Confirms the intent, returnin the user to the corresponding Enrollment
     * @param Enrollment $enrollment The enrollment, required for return URL
     * @param PaymentIntent $intent The intent to verify
     * @param Source $method Source to charge
     * @return PaymentIntent Updated intent
     */
    protected function confirmPaymentIntent(
        Enrollment $enrollment,
        PaymentIntent $intent,
        Source $source
    ): ?PaymentIntent {
        // Make sure it's still confirm-able
        if (
            $intent->status !== PaymentIntent::STATUS_REQUIRES_PAYMENT_METHOD &&
            $intent->status !== PaymentIntent::STATUS_REQUIRES_ACTION
        ) {
            throw new InvalidArgumentException("Intent cannot be confirmed right now", 1);
        }

        try {
            logger()->debug('Confirming {intent} using {source}', compact('intent', 'source'));
            // Confirm the intent on Stripe's end
            return $intent->confirm([
                'payment_method' => $source->id,
                'return_url' => route('enroll.pay-return', ['activity' => $enrollment->activity]),
            ]);
        } catch (ApiErrorException $error) {
            // Handle errors
            app(StripeErrorService::class)->handleCreate($error);

            // Return null if the error wasn't worthy of a throw (unlikely)
            return null;
        }
    }
}
