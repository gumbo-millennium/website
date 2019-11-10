<?php

namespace App\Listeners;

use App\Jobs\Stripe\RefundEnrollment;
use App\Models\Enrollment;
use App\Models\States\Enrollment\Cancelled;
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

        // Handle cancellation
        if ($event->finalState->is(Cancelled::class)) {
            if ($enrollment->price > 0 && $enrollment->payment_intent !== null) {
                dispatch(new RefundEnrollment($enrollment));
            }
        }
    }
}
