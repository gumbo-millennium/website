<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\States\Enrollment\Cancelled;
use App\Models\States\Enrollment\Paid;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Handles allowing mutations on enrollments.
 */
class EnrollmentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any enrollments.
     *
     * @return bool
     */
    public function viewAny(User $user)
    {
        return $user->can('manage', Activity::class);
    }

    /**
     * Determine whether the user can view the enrollment.
     *
     * @return bool
     */
    public function view(User $user, Enrollment $enrollment)
    {
        return $user->can('manage', $enrollment->activity);
    }

    /**
     * Determine whether the user can create enrollments.
     *
     * @return bool
     */
    public function create(User $user)
    {
        return $user->can('manage', Activity::class);
    }

    /**
     * Determine whether the user can update the enrollment.
     *
     * @return bool
     */
    public function update(User $user, Enrollment $enrollment)
    {
        return $user->can('manage', $enrollment->activity);
    }

    /**
     * Determine whether the user can refund the money paid for the enrollment.
     *
     * @return bool
     */
    public function refund(User $user, Enrollment $enrollment)
    {
        return $user->can('manage', $enrollment->activity);
    }

    /**
     * Returns if the user can unenroll.
     */
    public function unenroll(User $user, Enrollment $enrollment): bool
    {
        // Cancelling an already cancelled enrollment?
        if ($enrollment->state->is(Cancelled::class)) {
            logger()->info('Unenroll {enrollment} rejected. Already unenrolled.', compact('enrollment'));

            return false;
        }

        // Disallow cancelling paid enrollments
        if ($enrollment->state->is(Paid::class)) {
            logger()->info('Unenroll {enrollment} rejected. Already paid.', compact('enrollment'));

            return false;
        }

        // Get activity
        $activity = $enrollment->activity;

        // If the enrollment has not closed, unenrolling is fine.
        if ($activity->enrollment_end > now()) {
            logger()->info('Unenroll {enrollment} accepted. Within window.', compact('enrollment'));

            return true;
        }

        // We're after the enrollment window. The only possible way to unenroll now
        // is to have an expiring enrollment that's not stable (confirmed, paid or cancelled)
        // yet.
        $hasExpiration = $enrollment->expire !== null;
        $isStable = $enrollment->state->isStable();
        $judgement = $hasExpiration && ! $isStable;

        // Log as notice. We'll probably look for this.
        logger()->notice('User {user} wanted to unenroll after window. Result {judgement}', [
            'user' => $user,
            'enrollment' => $enrollment,
            'judgement' => $judgement,
            'has-expiration' => $hasExpiration,
            'is-stable' => $isStable,
        ]);

        // Return judgement
        return $judgement;
    }

    /**
     * Determine whether the user can delete the enrollment.
     *
     * @return false
     */
    public function delete()
    {
        // Deleting enrollments is not allowed
        return false;
    }

    /**
     * Can the given user manage the given enrollment or enrollments in general.
     */
    public function manage(User $user, ?Enrollment $enrollment = null): bool
    {
        // Get activity safely
        $activity = optional($enrollment)->activity;

        // Weird edge-case of an unlinked enrollment requires an admin
        if ($enrollment && ! $activity) {
            return $user->can('admin', Activity::class);
        }

        // Forward to ActivityPolicy
        return $user->can('manage', $activity ?? Activity::class);
    }

    /**
     * Can the given user admin enrollments.
     */
    public function admin(User $user): bool
    {
        return $user->can('admin', Activity::class);
    }
}
