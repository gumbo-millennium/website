<?php

declare(strict_types=1);

namespace App\Jobs\Stripe;

use App\Contracts\StripeServiceContract;
use App\Models\Enrollment;
use App\Models\States\Enrollment\Cancelled as CancelledState;
use App\Models\States\Enrollment\Paid as PaidState;
use Stripe\Invoice;

class PaymentValidationJob extends StripeJob
{
    /**
     * Enrollment to check.
     *
     * @var Enrollment
     */
    protected $enrollment;

    /**
     * @var StripeServiceContract
     */
    protected $service;

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
    public function handle(StripeServiceContract $service): void
    {
        // Shorthand
        $enrollment = $this->enrollment;

        // Skip if paid
        if ($enrollment->state->isOneOf(PaidState::class, CancelledState::class)) {
            logger()->info('Got validation on paid/cancelled enrollment. Stopping');

            return;
        }

        // Check source first (maybe consume it)
        $invoice = $service->getInvoice($enrollment);

        // Check invoice
        if (! $invoice) {
            logger()->info('Found no invoice for {enrollment}', compact('enrollment'));

            return;
        }

        // Paid, confirm enrollment
        if ($invoice->status === Invoice::STATUS_PAID) {
            logger()->info('Invoice is paid, marking {enrollment} as paid', compact('invoice', 'enrollment'));

            $newEnrollment = Enrollment::find($enrollment->id);
            $newEnrollment->transitionTo(PaidState::class)->saveOrFail();

            return;
        }

        // Voided, cancel enrollment
        if ($invoice->status === Invoice::STATUS_VOID) {
            logger()->info('Invoice is voided, marking {enrollment} as cancelled', compact('invoice', 'enrollment'));

            $newEnrollment = Enrollment::find($enrollment->id);
            $newEnrollment->transitionTo(CancelledState::class)->saveOrFail();

            return;
        }

        // try again in a bit
        $this->release(60);
    }
}
