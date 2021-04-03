<?php

declare(strict_types=1);

namespace Tests\Traits;

use App\Contracts\EnrollmentServiceContract;
use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\States\Enrollment\Cancelled;
use App\Models\States\Enrollment\Confirmed;
use App\Models\States\Enrollment\Created;
use App\Models\States\Enrollment\Paid;
use App\Models\States\Enrollment\Refunded;
use App\Models\States\Enrollment\Seeded;
use App\Models\User;
use Illuminate\Support\Facades\App;
use InvalidArgumentException;

trait TestsWithEnrollments
{
    /**
     * Returns the thingy that makes enrollments go vroom.
     * Helper for App::make not type-hinting.
     *
     * @return EnrollmentServiceContract
     */
    protected function getEnrollmentService(): EnrollmentServiceContract
    {
        return App::make(EnrollmentServiceContract::class);
    }

    /**
     * Enrolls the user into the activity, trying it's hardest to get to $wantedState, if set.
     *
     * @param Activity $activity
     * @param User $user
     * @param string|null $wantedState
     * @return Enrollment
     * @throws InvalidArgumentException Invalid or impossible states
     */
    protected function enrollUser(
        Activity $activity,
        User $user,
        ?string $wantedState = null
    ): Enrollment {
        $service = $this->getEnrollmentService();

        $enrollment = $service->createEnrollment($activity, $user);

        // Leave as-is
        if (!$wantedState || $wantedState === Created::class) {
            return $enrollment;
        }

        // Cancel it
        if ($wantedState === Cancelled::class) {
            $enrollment
                ->transitionTo(Cancelled::class)
                ->save();

            return $enrollment;
        }

        // Mark seeded
        $enrollment
            ->transitionTo(Seeded::class)
            ->save();

        // Return if ok
        if ($wantedState === Seeded::class) {
            return $enrollment;
        }

        // Mark as paid if non-free
        $enrollment
            ->transitionTo($enrollment->price !== null ? Paid::class : Confirmed::class)
            ->save();

        if (is_a($wantedState, Confirmed::class, true)) {
            return $enrollment;
        }

        if ($wantedState !== Refunded::class) {
            throw new InvalidArgumentException("Requested unknown state [$wantedState] on enrollment");
        }

        if ($enrollment->price === null) {
            throw new InvalidArgumentException("Requested refunded state on free enrollment");
        }

        $enrollment
            ->transitionTo(Refunded::class)
            ->save();

        return $enrollment;
    }
}
