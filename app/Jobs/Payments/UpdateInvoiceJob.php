<?php

declare(strict_types=1);

namespace App\Jobs\Payments;

use App\Models\Invoice;
use App\Models\States\Enrollment\Cancelled;
use App\Models\States\Enrollment\Paid;
use App\Services\Payments\ProviderFactory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Super abstract job to handle webhooks and other events that are likely to have triggered an invoice update.
 * Marks Enrollments as Paid and Cancelled pending behaviour
 */
class UpdateInvoiceJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;


    protected Invoice $invoice;
    /**
     * Create a new job instance.
     * @return void
     */
    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    /**
     * Execute the job.
     * @return void
     */
    public function handle(ProviderFactory $providerFactory)
    {
        // Preload content
        $invoice = $this->invoice;
        $invoice->loadMissing(['enrollment', 'user', 'enrollment.activity']);

        // Prep some shorthands
        $enrollment = $invoice->enrollment;

        // Get invoice factory
        $provider = $providerFactory->getProvider($enrollment);

        // Ask provider to update
        if (!$provider->downloadInvoiceUpdates($invoice)) {
            return;
        }

        // Check invoice
        $invoice->refresh();

        // Unenroll if the invoice was refunded
        if (!$enrollment->state instanceof Cancelled && $invoice->refunded) {
            $enrollment->transitionTo(Cancelled::class);
            $enrollment->save();
            return;
        }

        // Mark as paid if the invoice is paid
        if ($enrollment->wanted_state instanceof Paid && $invoice->paid) {
            $enrollment->transitionTo(Paid::class);
            $enrollment->save();
        }
    }
}
