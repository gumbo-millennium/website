<?php

declare(strict_types=1);

namespace App\Services\Payments\Parts\Mollie;

use App\Helpers\Arr;
use App\Models\Enrollment;
use App\Models\Invoice;
use App\Models\PaymentMethods\PaymentMethod;
use Illuminate\Contracts\Container\BindingResolutionException;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\MollieApiClient;
use Mollie\Api\Resources\Order as MollieInvoice;
use Mollie\Api\Resources\Payment;

/**
 * Handles retrieval and parsing of Mollie payment methods
 */
trait HandlesInvoices
{
    /**
     * Returns the Mollie Invoice (order) that matches this invoice
     * @param Invoice $invoice
     * @return null|MollieInvoice
     * @throws BindingResolutionException
     */
    protected function getMollieInvoice(Invoice $invoice): ?MollieInvoice
    {
        // Get API
        $api = $this->getMollieApi();

        // Find the invoice
        try {
            return $api->orders->get($invoice->provider_id, ['embed' => 'payments']);
        } catch (ApiException $exception) {
            \report($exception);
            return null;
        }
    }
    /**
     * Creates a new invoice at Mollie for the given enrollment
     * @param Enrollment $enrollment
     * @param PaymentMethod $method
     * @return MollieInvoice
     */
    protected function createMollieInvoice(Enrollment $enrollment, PaymentMethod $method): MollieInvoice
    {
        // Get base data
        $orderData = $this->buildInvoiceData($enrollment);

        // Get payment object
        $paymentData = $this->buildInvoicePayment($enrollment, $method);

        // Combine the two
        $realData = array_filter(\array_merge($orderData, $paymentData));

        // Create invoice
        $api = $this->getMollieApi();
        return $api->orders->create($realData);
    }

    /**
     * Updates the payment method on the invoice, by cancelling currently pending forms.
     * @param Invoice $invoice
     * @param PaymentMethod $method
     * @return bool
     * @throws BindingResolutionException
     */
    protected function updateMollieInvoice(Invoice $invoice, PaymentMethod $method): bool
    {
        // Get API
        $api = $this->getMollieApi();

        // Find the invoice
        $apiInvoice = $this->getMollieInvoice($invoice);

        // Fail if missing
        if (!$apiInvoice) {
            return false;
        }

        // Get some vars
        $enrollment = $invoice->enrollment;

        // Iterate items
        foreach ($apiInvoice->payments() as $payment) {
            \assert($payment instanceof Payment);

            // cancel open payments
            if ($payment->isCancelable === true) {
                $api->payments->cancel($payment->id);
            }

            // Non-cancellable, abort (this can be paid, pending or whatever, but
            // we can't start a new one yet).
            return false;
        }

        // Get payment object
        $paymentData = $this->buildInvoicePayment($enrollment, $method);

        // Re-map for new payment creation (data should be separate, not bundled)
        $paymentInnerData = Arr::pull($paymentData, 'payment');
        $paymentData = array_merge($paymentData, $paymentInnerData);

        // Create a new payment and pass if it's created
        return $apiInvoice->createPayment($paymentData) instanceof Payment;
    }

    abstract protected function getMollieApi(): MollieApiClient;
}
