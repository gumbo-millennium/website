<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Activity;
use App\Models\States\Enrollment\Paid as PaidState;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Permission policy of the Activity model
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class ActivityPolicy
{
    // phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter
    use HandlesAuthorization;

    public const CREATE_PERMISSION = 'activity-create';
    public const ADMIN_PERMISSION = 'activity-admin';
    public const PURGE_PERMISSION = 'activity-purge';

    /**
     * Returns if the user is the owner of the given activity.
     *
     * @param User $user
     * @param Activity $activity
     * @return bool
     */
    private static function isOwner(User $user, Activity $activity): bool
    {
        return $activity->role && $user->hasRole($activity->role);
    }

    /**
     * Returns if the user is the owner of any activity.
     *
     * @param User $user
     * @return bool
     */
    private static function isAnyOwner(User $user): bool
    {
        return $user->getHostedActivityQuery(Activity::query())->exists();
    }

    /**
     * Determine whether the user can view any activities.
     *
     * @param  User  $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        // Anyone can view activities
        return $user->can('manage', Activity::class) || $user->can('create', Activity::class);
    }

    /**
     * Determine whether the user can view the activity.
     *
     * @param  User  $user
     * @param  Activity  $activity
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function view(?User $user, Activity $activity): bool
    {
        // Anyone can see activities
        return $activity->is_public || ($user && $user->is_member);
    }

    /**
     * Can this user enroll
     *
     * @param User $user
     * @param Activity $activity
     * @return bool
     */
    public function enroll(User $user, Activity $activity): bool
    {
        // Non-public activities cannot be enrolled by guests
        if (!$user->is_member && !$activity->is_public) {
            return false;
        }

        // Cannot enroll when there is no more room
        if ($activity->available_seats <= 0) {
            return false;
        }

        // Cannot enroll if it's cancelled
        if ($activity->is_cancelled) {
            return false;
        }

        // Allow
        return true;
    }

    /**
     * Determine whether the user can create activities.
     *
     * @param  User  $user
     * @return bool
     */
    public function create(User $user): bool
    {
        // Check creation
        return $user->can('admin', Activity::class) || $user->hasPermissionTo(self::CREATE_PERMISSION);
    }

    /**
     * Determine whether the user can update the activity.
     *
     * @param  User  $user
     * @param  Activity  $activity
     * @return bool
     */
    public function update(User $user, Activity $activity): bool
    {
        // Can't edit activities that have ended
        if ($activity->end_date < now()) {
            return false;
        }

        // Check update
        return $user->can('manage', $activity);
    }

    /**
     * Determine whether the user can delete the activity.
     *
     * @param  User  $user
     * @param  Activity  $activity
     * @return bool
     */
    public function cancel(User $user, Activity $activity): bool
    {
        // Can't cancel activities that have already started
        if ($activity->end_date < now() || $activity->is_cancelled) {
            return false;
        }

        // Identical to update
        return $user->can('manage', $activity);
    }

    /**
     * Determine whether the user can delete the activity.
     *
     * @param  User  $user
     * @param  Activity  $activity
     * @return bool
     */
    public function delete(User $user, Activity $activity): bool
    {
        // Prevent deletion if not allowed
        if (!$user->can('admin', $activity)) {
            return false;
        }

        // Disallow if there are paid enrollments
        if ($activity->enrollments()->whereState('state', PaidState::class)->exists()) {
            return false;
        }

        // Allow if created less than 1 hour ago
        if ($activity->created_at > now()->subHour()) {
            return true;
        }

        // Allow if cancelled and would've ended more than 1 month ago
        if ($activity->is_cancelled && $activity->end_date < now()->subMonth()) {
            return true;
        }

        // Allow if ended more than 1 year ago
        if ($activity->end_date < now()->subYear()) {
            return true;
        }

        // Disallow in other scenarios
        return false;
    }

    /**
     * Determine whether the user can restore the activity.
     *
     * @param  User  $user
     * @param  Activity  $activity
     * @return bool
     */
    public function restore(User $user, Activity $activity): bool
    {
        // Can't restore activities that happened in the past
        if ($activity->start_date < now()) {
            return false;
        }

        // Check if restoration is possible
        return $user->can('manage', $activity);
    }

    /**
     * Determine whether the user can permanently delete the activity.
     *
     * @param  User  $user
     * @param  Activity  $activity
     * @return bool
     */
    public function forceDelete(User $user, Activity $activity): bool
    {
        // Disallow deletion if the user can't manage
        if ($user->can('manage', $activity)) {
            return false;
        }

        // Don't allow deletion of activities that have payments within the
        // last 7 years, unless the user can purge
        return $activity->payments()->where('updated_at', '>', today()->subYears(7))
            || $user->hasPermissionTo(self::PURGE_PERMISSION);
    }

    /**
     * Allow linking an enrollment if the user is a manager of the event
     *
     * @param User $user
     * @param Activity $activity
     * @return bool
     */
    public function addEnrollment(User $user, Activity $activity): bool
    {
        return $user->can('manage', $activity);
    }

    /**
     * Can the given user manage the given activities or activities in general
     *
     * @param User $user
     * @param Activity|null $activity
     * @return bool
     */
    public function manage(User $user, ?Activity $activity = null): bool
    {
        return $user->can('admin', $activity ?? Activity::class)
            || ($activity ? self::isOwner($user, $activity) : self::isAnyOwner($user));
    }

    /**
     * Can the user perform admin actions on this object
     *
     * @param User $user
     * @return bool
     */
    public function admin(User $user): bool
    {
        return $user->hasPermissionTo(self::ADMIN_PERMISSION);
    }
}
