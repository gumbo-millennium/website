<?php

namespace App\Jobs\Stripe;

use App\Models\Enrollment;
use App\Models\States\Enrollment\Cancelled;
use App\Models\States\Enrollment\Confirmed;
use App\Models\States\Enrollment\Seeded;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Stripe\Exception\UnknownApiErrorException;
use Stripe\PaymentIntent;
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
        // Abort if Stripe key isn't set
        if (empty(Stripe::getApiKey())) {
            return;
        }

        // Shorthand
        $enrollment = $this->enrollment;

        // Check if enrollment still matters and has a payment intent
        if ($enrollment->payment_intent) {
            return;
        }

        // Get intetn
        try {
            $intent = PaymentIntent::retrieve($enrollment->payment_intent);
        } catch (UnknownApiErrorException $e) {
            // If there's a 404 we don't act.
            if ($e->getHttpStatus() === 404) {
                return;
            }

            // Bubble otherwise
            throw $e;
        }

        // Refresh the enrollment, in case the API call took some time
        $enrollment->refresh();

        // Disallow changing cancelled states
        if ($enrollment->state->is(Cancelled::class)) {
            return;
        }

        // Check if the intent is paid, and if the enrollment state allows
        // migrating to a paid state.
        if (
            $intent->status === PaymentIntent::STATUS_SUCCEEDED &&
            $enrollment->state->isOneOf([Seeded::class, Confirmed::class])
        ) {
            // Change state to paid
            $enrollment->state->transitionTo(Paid::class);
            $enrollment->save();
        }
    }
}
