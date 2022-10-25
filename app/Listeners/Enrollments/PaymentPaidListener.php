<?php

declare(strict_types=1);

namespace App\Listeners\Enrollments;

use App\Events\Payments\PaymentPaid;
use App\Models\Enrollment;
use App\Models\States\Enrollment as States;

class PaymentPaidListener
{
    /**
     * Handle the event.
     */
    public function handle(PaymentPaid $event): void
    {
        // Get subject
        $payment = $event->getPayment();
        $subject = $payment->payable;

        if (! $subject instanceof Enrollment) {
            return;
        }

        if ($subject->canTransitionTo(States\Paid::class)) {
            $subject->state->transitionTo(States\Paid::class);
            $subject->save();
        }
    }
}
