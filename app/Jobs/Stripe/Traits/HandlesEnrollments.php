<?php

declare(strict_types=1);

namespace App\Jobs\Stripe\Traits;

use App\Models\Enrollment;
use App\Models\States\Enrollment\Cancelled;

/**
 * Utility methods for Charge objects.
 */
trait HandlesEnrollments
{
    /**
     * Cancels the given enrollment. A refund will be automatically generated.
     *
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
