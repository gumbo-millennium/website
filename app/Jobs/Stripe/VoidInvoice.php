<?php

namespace App\Jobs\Stripe;

use App\Models\Enrollment;
use App\Models\States\Enrollment\Cancelled;
use App\Models\States\Enrollment\Paid;
use App\Services\StripeService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use LogicException;
use Stripe\CreditNote;
use Stripe\Exception\UnknownApiErrorException;
use Stripe\Invoice;
use Stripe\PaymentIntent;
use Stripe\Refund;
use Stripe\Stripe;

class VoidInvoice implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Undocumented variable
     *
     * @var Enrollment
     */
    protected $enrollment;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Enrollment $enrollment)
    {
        $this->enrollment = $enrollment;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(StripeService $service)
    {
        // Abort if Stripe key isn't set
        if (empty(Stripe::getApiKey())) {
            return;
        }

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
        /** @var Invoice $invoice */
        $invoice = $service->get(Invoice::class, $enrollment->payment_invoice);

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
                'Cannot void paid invoice {invoice}, issuing a refund instead',
                compact('enrollment', 'invoice')
            );
            RefundEnrollment::dispatch($invoice);
            return;
        }

        // Void the invoice if it's possible
        if (in_array($invoice->status, [Invoice::STATUS_OPEN, Invoice::STATUS_UNCOLLECTIBLE])) {
            logger()->info('Voiding invoice {invoice}', [
                'enrollment' => $enrollment,
                'invoice' => $invoice
            ]);
            $invoice->voidInvoice();
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

        logger()->error('Invoice status {status} is uknown for this {invoice}', [
            'status' => $invoice->status,
            'enrollment' => $enrollment,
            'invoice' => $invoice
        ]);

        throw new LogicException("Unknown invoice status {$invoice->status}");
    }
}
