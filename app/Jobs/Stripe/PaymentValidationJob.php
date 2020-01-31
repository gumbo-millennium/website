<?php

namespace App\Jobs\Stripe;

use App\Contracts\StripeServiceContract;
use App\Models\Enrollment;
use App\Models\States\Enrollment\Cancelled as CancelledState;
use App\Models\States\Enrollment\Paid as PaidState;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Stripe\Invoice;
use Stripe\Source;
use Stripe\Stripe;

class PaymentValidationJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Enrollment to check
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
     *
     * @return void
     */
    public function handle(StripeServiceContract $service): void
    {
        // Abort if Stripe key isn't set
        if (empty(Stripe::getApiKey())) {
            return;
        }

        // Shorthand
        $enrollment = $this->enrollment;

        // Skip if paid
        if ($enrollment->state instanceof PaidState || $enrollment->state instanceof CancelledState) {
            logger()->info('Got validation on paid/cancelled enrollment. Stopping');
            return;
        }

        // Check source first (maybe consume it)
        $source = $service->getSource($enrollment, null);
        $invoice = $service->getInvoice($enrollment);

        if (!($invoice || !$invoice->paid) && $source && $source->status === Source::STATUS_CHARGEABLE) {
            logger()->info('Attempting to pay {invoice} with {source}', compact('invoice', 'source', 'enrollment'));
            $invoice = $service->payInvoice($enrollment, $source);
        }

        // Check invoice
        if (!$invoice) {
            logger()->info('Found no invoice for {enrollment}', compact('source', 'enrollment'));
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

        // Debug postpone
        logger()->info('No result yet, postponing', compact('invoice', 'enrollment'));

        // try again in a bit
        $this->release(60);
    }
}
