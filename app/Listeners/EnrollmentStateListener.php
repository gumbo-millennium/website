<?php

namespace App\Listeners;

use App\Jobs\Stripe\RefundEnrollment;
use App\Jobs\Stripe\VoidInvoice;
use App\Models\Enrollment;
use App\Models\States\Enrollment\Cancelled;
use App\Models\States\Enrollment\Paid;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Spatie\ModelStates\Events\StateChanged;

class EnrollmentStateListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  StateChanged  $event
     * @return void
     */
    public function handle(StateChanged $event)
    {
        // Don't act on enrollments
        if (!$event->model instanceof Enrollment) {
            return;
        }

        // Get shorthand
        $enrollment = $event->model;
        $finalState = $enrollment->state;

        // Handle cancellation
        if ($finalState instanceof Cancelled) {
            if ($enrollment->payment_invoice) {
                // Void the invoice if the enrollment wasn't paid yet
                VoidInvoice::dispatch($enrollment);
            }
        }
    }
}
