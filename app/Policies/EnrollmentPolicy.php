<?php

namespace App\Policies;

use App\Models\Activity;
use App\Models\User;
use App\Models\Enrollment;
use App\Models\Payment;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Database\Eloquent\Builder;

/**
 * Handles allowing mutations on enrollments
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class EnrollmentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any enrollments.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function viewAny(User $user)
    {
        return $user->can('manage', Activity::class);
    }

    /**
     * Determine whether the user can view the enrollment.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Enrollment  $enrollment
     * @return bool
     */
    public function view(User $user, Enrollment $enrollment)
    {
        return $user->can('manage', $enrollment->activity);
    }

    /**
     * Determine whether the user can create enrollments.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function create(User $user)
    {
        return $user->can('manage', Activity::class);
    }

    /**
     * Determine whether the user can update the enrollment.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Enrollment  $enrollment
     * @return bool
     */
    public function update(User $user, Enrollment $enrollment)
    {
        return $user->can('manage', $enrollment->activity);
    }

    /**
     * Determine whether the user can refund the money paid for the enrollment.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Enrollment  $enrollment
     * @return bool
     */
    public function refund(User $user, Enrollment $enrollment)
    {
        return $user->can('manage', $enrollment->activity);
    }

    /**
     * Determine whether the user can delete the enrollment.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Enrollment  $enrollment
     * @return bool
     */
    public function delete(User $user, Enrollment $enrollment)
    {
        // Allow deletion if purging is allowed, or if the enrollment is aged
        // enough.
        return $user->hasPermissionTo(ActivityPolicy::PURGE_PERMISSION)
            || $enrollment->payment()->count() === 0
            || $enrollment->payment()->latest()->pluck('created_at')->first() < today()->subYears(7);
    }

    /**
     * Never allow attaching payments
     *
     * @param User $user
     * @param Enrollment $enrollment
     * @return bool
     */
    public function addPayment(User $user, Enrollment $enrollment)
    {
        return $user->can('admin', Payment::class);
    }

    /**
     * Can the given user manage the given enrollment or enrollments in general
     *
     * @param User $user
     * @param Enrollment $enrollment
     * @return bool
     */
    public function manage(User $user, Enrollment $enrollment = null): bool
    {
        // Get activity safely
        $activity = optional($enrollment)->activity;

        // Weird edge-case of an unlinked enrollment requires an admin
        if ($enrollment && !$activity) {
            return $user->can('admin', File::class);
        }

        // Forward to ActivityPolicy
        return $user->can('manage', $activity ?? Activity::class);
    }
}
