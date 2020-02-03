<?php

declare(strict_types=1);

namespace App\Jobs\Stripe;

use App\Models\Enrollment;
use App\Services\StripeService;
use Stripe\Invoice;

class VoidInvoice extends StripeJob
{
    /**
     * Undocumented variable
     */
    protected Enrollment $enrollment;

    /**
     * Create a new job instance.
     * @return void
     */
    public function __construct(Enrollment $enrollment)
    {
        $this->enrollment = $enrollment;
    }

    /**
     * Execute the job.
     */
    public function handle(StripeService $service): void
    {
        // Shorthand
        $enrollment = $this->enrollment;

        // Stop if enrollment is free or not cancelled
        if (!$enrollment->payment_invoice) {
            logger()->info('Enrollment {enrollment} does not have an invoice.', [
                'enrollment' => $enrollment,
            ]);
            return;
        }

        // Get data from Stripe
        $invoice = $service->getInvoice($enrollment);
        \assert($invoice instanceof Invoice);

        // Invoices voided in the Stripe dashboard might cause a trigger to end up here.
        if ($invoice->status === Invoice::STATUS_VOID) {
            logger()->info(
                'Cannot void already voided invoice {invoice}. Doing nothing.',
                compact('enrollment', 'invoice')
            );
            return;
        }

        // Issue a refund if the user has already paid the invoice.
        if ($invoice->status === Invoice::STATUS_PAID) {
            logger()->info(
                'Cannot void paid invoice {invoice}.',
                compact('enrollment', 'invoice')
            );
            return;
        }

        // Delete the invoice if it's not finalized
        if ($invoice->status === Invoice::STATUS_DRAFT) {
            logger()->info('Deleting draft-invoice {invoice}', [
                'enrollment' => $enrollment,
                'invoice' => $invoice
            ]);
            $invoice->delete();
            return;
        }

        // Void the invoice if it's possible
        \assert(in_array($invoice->status, [Invoice::STATUS_OPEN, Invoice::STATUS_UNCOLLECTIBLE]), "Invoice in unknown ste {$invoice->status}");

        // Void invoice
        logger()->info('Voiding invoice {invoice}', [
            'enrollment' => $enrollment,
            'invoice' => $invoice
        ]);
        $invoice->voidInvoice();
    }
}
