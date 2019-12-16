<?php

declare(strict_types=1);

namespace App\Http\Controllers\Activities\Traits;

use App\Jobs\Stripe\CustomerUpdateJob;
use App\Models\Enrollment;
use App\Services\StripeErrorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use LogicException;
use RuntimeException;
use Stripe\Exception\ApiErrorException;
use Stripe\Invoice;
use Stripe\InvoiceItem;
use Stripe\Mandate;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use Stripe\Source;

/**
 * Creates Billing Invoices in Stripe
 */
trait HandlesInvoices
{
    use FormatsStripeData;
    use HandlesCustomers;

    /**
     * Creates a Billing Invoice at Stripe and returns it.
     * Returns null if $enrollment is a free activity (for this user)
     *
     * @param Enrollment $enrollment
     * @return Invoice|null
     */
    protected function createPaymentInvoice(Enrollment $enrollment): ?Invoice
    {
        // Return null if price is empty
        if (empty($enrollment->price)) {
            return null;
        }

        // Make sure we have a user
        $this->ensureCustomerExists($enrollment);

        // Due in 7 days by default
        $dueDate = today()->addWeek();

        if ($enrollment->activity->start_date < now()) {
            // Due immediately when activity has started
            $dueDate = today();
        } elseif ($enrollment->activity->start_date < $dueDate) {
            // Due when event starts otherwise
            $dueDate = $enrollment->activity->start_date;
        }

        // Build info
        $sharedInfo = $this->getEnrollmentInformation($enrollment);
        $invoiceInfo = array_merge(
            Arr::only($sharedInfo, ['receipt_email', 'statement_descriptor', 'metadata']),
            [
                'customer' => $enrollment->user->stripe_customer_id,
                'payment_method_types' => ['ideal'],
                'collection_method' => 'send_invoice',
                'due_date' => $dueDate,
            ]
        );

        // Build invoice products
        $invoiceItems = [
            [
                'amount' => $enrollment->price,
                'description' => Arr::get($sharedInfo, 'description', $enrollment->title),
            ],
            [
                'amount' => $enrollment->total_price - $enrollment->price,
                'description' => 'Transactiekosten',
                'discountable' => false
            ]
        ];

        try {
            // Create Invoice
            $invoice = Invoice::create($invoiceInfo);

            // Create products
            $createdLines = [];
            foreach ($invoiceItems as $item) {
                $createdLines[] = InvoiceItem::create(array_merge([
                    'currency' => 'EUR',
                    'customer' => $invoice->customer,
                    'invoice' => $invoice->id
                ], $item))->id;
            }

            // Delete lines not supposed to be on this invoice
            $invoiceLines = $invoice->lines->all(['limit' => 20]);
            foreach ($invoiceLines as $line) {
                if (!in_array($line->id, $createdLines)) {
                    $line->delete();
                }
            }

            // Return invoice
            return $invoice;
        } catch (ApiErrorException $error) {
            app(StripeErrorService::class)->handleCreate($error);
        }
    }
    /**
     * Creates a Payment Intent at Stripe, returns the ID.
     * Returns null if $enrollment is a free activity (for this user)
     *
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
     *
     * @param Enrollment $enrollment The enrollment, required for return URL
     * @param PaymentIntent $intent The intent to verify
     * @param PaymentMethod $method Method to pay
     * @return PaymentIntent Updated intent
     */
    protected function confirmPaymentIntent(
        Enrollment $enrollment,
        PaymentIntent $intent,
        PaymentMethod $method
    ): ?PaymentIntent {
        // Make sure it's still confirm-able
        if (
            $intent->status !== PaymentIntent::STATUS_REQUIRES_PAYMENT_METHOD &&
            $intent->status !== PaymentIntent::STATUS_REQUIRES_ACTION
        ) {
            throw new InvalidArgumentException("Intent cannot be confirmed right now", 1);
        }

        try {
            // Confirm the intent on Stripe's end
            return $intent->confirm([
                'payment_method' => $method->id,
                'return_url' => route('payment.complete', ['activity' => $enrollment->activity]),
            ]);
        } catch (ApiErrorException $error) {
            // Handle errors
            app(StripeErrorService::class)->handleCreate($error);

            // Return null if the error wasn't worthy of a throw (unlikely)
            return null;
        }
    }

    /**
     * Builds a redirect to Stripe, if applicable. Returns null otherwise.
     *
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
}
