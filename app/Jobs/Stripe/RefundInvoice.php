<?php

declare(strict_types=1);

namespace App\Jobs\Stripe;

use App\Models\Enrollment;
use App\Models\States\Enrollment\Cancelled;
use App\Services\StripeService;
use InvalidArgumentException;
use UnderflowException;

class RefundInvoice extends StripeJob
{
    /**
     * Undocumented variable
     */
    protected Enrollment $enrollment;

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

        // Mark as cancelled if not yet so
        if (!($enrollment->state instanceof Cancelled)) {
            $enrollment->transitionTo(Cancelled::class, 'state');
            $enrollment->save();
        }

        try {
            // Get the invoice
            $invoice = $service->getInvoice($enrollment, StripeService::OPT_NO_CREATE);

            // Create a full refund
            $refund = $service->createRefund($enrollment, $enrollment->deleted_reason, null);

            // Log result
            logger()->info('Refunded {invoice} using {refund}', compact('refund', 'invoice'));
        } catch (UnderflowException $exception) {
            // Log warning
            logger()->warning('Trying to refund already-refunded payment', [
                'exception' => $exception,
            ]);

            // Complete the job
            return;
        } catch (InvalidArgumentException $exception) {
            // Log warning
            logger()->warning('Something went wrong  when refunding the enrollment', [
                'exception' => $exception,
            ]);

            // Complete the job
            return;
        }
    }
}
