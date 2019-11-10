<?php

namespace App\Jobs\Stripe;

use App\Models\Enrollment;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Stripe\Exception\UnknownApiErrorException;
use Stripe\PaymentIntent;
use Stripe\Refund;

class RefundEnrollment implements ShouldQueue
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
    public function handle()
    {
        // Get enrollment and skip if it's not paid
        $enrollment = $this->enrollment;
        if (!$enrollment->price || empty($enrollment->payment_intent)) {
            return;
        }

        // Get data from Stripe
        $intent = null;
        try {
            $intent = PaymentIntent::retrieve($enrollment->payment_intent);
        } catch (UnknownApiErrorException $e) {
            // 404 is not an error
            if ($e->getHttpStatus() === 404) {
                return;
            }

            // Throw up otherwise
            throw $e;
        }

        logger()->notice('Issuing refund for {enrollment}', compact('enrollment'));

        // Issue a refund if we've got money to return. This will automatically cancel
        // the Payment Intent
        if ($intent->amount_received > 0) {
            // Create refund
            $refund = Refund::create([
                'payment_intent' => $intent->id,
                'reason' => Refund::REASON_REQUESTED_BY_CUSTOMER,
                'amount' => $intent->amount_received,
                'description' => "Inschrijving voor {$enrollment->activity->name} geannuleerd.",
                'metadata' => [
                    'activity-id' => $enrollment->activity_id,
                    'user-id' => $enrollment->user_id,
                    'enrollment-id' => $enrollment->enrollment_id,
                ]
            ]);

            // Log result
            logger()->info('Issued refund for {enrollment}: {refund_id}', [
                'refund_id' => $refund->id,
                'amount' => $refund->amount
            ]);
            return;
        }

        // Cancel the intent
        $intent->cancel([
            'cancellation_reason' => Refund::REASON_REQUESTED_BY_CUSTOMER
        ]);

        // Log result
        logger()->info('Cancelled transaction {intent_id}', [
            'intent_id' => $intent->id,
            'intent' => $intent
        ]);
    }
}
