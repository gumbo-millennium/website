<?php

declare(strict_types=1);

namespace App\Http\Controllers\Activities\Traits;

use App\Helpers\Arr;
use App\Models\Enrollment;
use App\Services\StripeErrorService;
use Stripe\Exception\ApiErrorException;
use Stripe\Invoice;
use Stripe\InvoiceItem;
use Stripe\PaymentIntent;

/**
 * Creates Billing Invoices in Stripe
 */
trait HandlesInvoices
{
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
                'customer' => $enrollment->user->stripe_id,
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
                'discountable' => false,
            ],
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
                    'invoice' => $invoice->id,
                ], $item))->id;
            }

            // Delete lines not supposed to be on this invoice
            $invoiceLines = $invoice->lines->all(['limit' => 20]);
            foreach ($invoiceLines as $line) {
                if (in_array($line->id, $createdLines)) {
                    continue;
                }

                $line->delete();
            }

            // Return invoice
            return $invoice;
        } catch (ApiErrorException $error) {
            app(StripeErrorService::class)->handleCreate($error);
        }
    }
    /**
     * Retrieves or creates invoice for the given enrollment.
     * Returns null if this user need not pay.
     *
     * @param Enrollment $enrollment
     * @return Stripe\Invoice|null
     * @throws BindingResolutionException
     * @throws ExceptionInvalidArgumentException
     */
    protected function getPaymentInvoice(Enrollment $enrollment): ?Invoice
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
}
