<?php

declare(strict_types=1);

namespace App\Jobs\Stripe\Hooks;

use App\Contracts\StripeServiceContract;
use App\Models\Enrollment;
use App\Models\States\Enrollment\Cancelled;
use App\Models\States\Enrollment\Paid;
use Stripe\Source;

/**
 * Handles paid invoices, in case people pay out-of-band (via SEPA transfer or something).
 *
 * Called on source.chargeable
 */
class HandleSourceChargeable extends StripeWebhookJob
{
    /**
     * Execute the job.
     */
    protected function process(?Source $source): void
    {
        $enrollment = Enrollment::wherePaymentSource($source->id)->first();
        // Check if the payment intent exists
        \assert($enrollment instanceof Enrollment);

        // Skip if not found
        if ($enrollment === null) {
            logger()->info(
                'Recieved chargeable {source} for unknown enrollment',
                compact('source'),
            );

            return;
        }

        // If the enrollment is already cancelled, don't do anything
        if ($enrollment->state instanceof Cancelled) {
            logger()->info(
                'Recieved chargeable {source} for cancelled enrollment',
                compact('source', 'enrollment'),
            );

            // Stop
            return;
        }

        // Don't act on already paid invoices.
        if ($enrollment->state instanceof Paid) {
            logger()->info(
                'Recieved chargeable {source} for already paid enrollment',
                compact('source', 'enrollment'),
            );

            // noop
            return;
        }

        $service = app(StripeServiceContract::class);
        \assert($service instanceof StripeServiceContract);

        $invoice = $service->getInvoice($enrollment, StripeServiceContract::OPT_NO_CREATE);
        \assert($invoice instanceof \Stripe\Invoice);
        if (! $invoice) {
            logger()->notice(
                'Recieved chargeable {source} for enrollment without invoice',
                compact('source', 'enrollment'),
            );

            // noop
            return;
        }

        if ($invoice->amount_remaining > $source->amount) {
            logger()->notice(
                'Recieved chargeable {source} for {invoice} of insufficient amount',
                compact('source', 'enrollment', 'invoice'),
            );

            // noop
            return;
        }

        // Log result
        logger()->info(
            'Paying {invoice} with {source}.',
            compact('enrollment', 'invoice', 'source'),
        );

        // Try to pay
        $service->payInvoice($enrollment, $source);
    }
}
