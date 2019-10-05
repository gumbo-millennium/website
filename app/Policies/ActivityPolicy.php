<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Activity;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Permission policy of the Activity model
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class ActivityPolicy
{
    use HandlesAuthorization;

    /**
     * @var string
     */
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
    public function viewAny(User $user)
    {
        // Anyone can view activities
        return $user->can('manage', Activity::class);
    }

    /**
     * Determine whether the user can view the activity.
     *
     * @param  User  $user
     * @param  Activity  $activity
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function view(User $user, Activity $activity)
    {
        // Anyone can see activities
        return true;
    }

    /**
     * Determine whether the user can create activities.
     *
     * @param  User  $user
     * @return bool
     */
    public function create(User $user)
    {
        // Check creation
        return $user->can('admin', Activity::class);
    }

    /**
     * Determine whether the user can update the activity.
     *
     * @param  User  $user
     * @param  Activity  $activity
     * @return bool
     */
    public function update(User $user, Activity $activity)
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
    public function cancel(User $user, Activity $activity)
    {
        // Can't cancel activities that have already started
        if ($activity->start_date < now()) {
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
    public function delete(User $user, Activity $activity)
    {
        // Can't delete activities that have already started
        if ($activity->start_date < now()) {
            return false;
        }

        // Delete is only allowed if there are no enrollments yet.
        if ($activity->enrollments()->whereNull('deleted_at')->count() == 0) {
            return $user->can('manage', $activity);
        }

        // Allow delete only if the permission is granted
        return $user->can('admin', $activity);
    }

    /**
     * Determine whether the user can restore the activity.
     *
     * @param  User  $user
     * @param  Activity  $activity
     * @return bool
     */
    public function restore(User $user, Activity $activity)
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
    public function forceDelete(User $user, Activity $activity)
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
    public function addEnrollment(User $user, Activity $activity)
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
    public function manage(User $user, Activity $activity = null): bool
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
