<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Jobs\Stripe\RefundInvoice;
use App\Jobs\Stripe\VoidInvoice;
use App\Models\Enrollment;
use App\Models\States\Enrollment\Cancelled;
use App\Models\States\Enrollment\Paid;
use App\Notifications\EnrollmentCancelled;
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
        $user = $enrollment->user;

        // Handle cancellation
        if (!($finalState instanceof Cancelled)) {
            return;
        }

        // Send cancellation notice
        $user->notify(new EnrollmentCancelled($enrollment));

        // Check if paid
        if ($event->initialState instanceof Paid) {
            // Refund the invoice
            RefundInvoice::dispatch($enrollment);
        } elseif ($enrollment->payment_invoice) {
            // Void the invoice if the enrollment wasn't paid yet
            VoidInvoice::dispatch($enrollment);
        }
    }
}
