<?php

namespace App\Jobs\Stripe\Traits;

use App\Models\Enrollment;
use App\Models\States\Enrollment\Cancelled;
use App\Services\StripeService;
use Spatie\ModelStates\Exceptions\InvalidConfig;
use Spatie\ModelStates\Exceptions\CouldNotPerformTransition;
use Stripe\Charge;
use Stripe\Event;

/**
 * Utility methods for Charge objects
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
trait HandlesEnrollments
{
    /**
     * Cancels the given enrollment. A refund will be automatically generated
     *
     * @param Enrollment $enrollment
     * @return void
     * @throws InvalidConfig
     * @throws CouldNotPerformTransition
     */
    private function cancelEnrollment(Enrollment $enrollment): void
    {
        if ($enrollment->state instanceof Cancelled) {
            logger()->info(
                'Tried to cancel already-cancelled enrollment {enrollment}',
                compact('enrollment')
            );
            return;
        }

        // Mark enrollment as cancelled
        $enrollment->state->transitionTo(Cancelled::class);
        $enrollment->save();

        // Log result
        logger()->info(
            'Marked {enrollment} as cancelled.',
            compact('enrollment')
        );
    }
}
