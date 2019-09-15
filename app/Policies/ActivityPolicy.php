<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Activity;
use Illuminate\Auth\Access\HandlesAuthorization;

class ActivityPolicy
{
    use HandlesAuthorization;

    /**
     * Returns if the user can alter the given activity
     *
     * @param User $user
     * @param Activity $activity
     * @return bool
     */
    public static function isOwningUser(User $user, Activity $activity) : bool
    {
        return ($activity->user && $user->is($activity->user))
            || ($activity->role && $user->hasRole($activity->role));
    }

    /**
     * Returns if the user has the given permission or is able to manage
     * the activity via ownership
     *
     * @param string $permission
     * @param User $user
     * @param Activity $activity
     * @return bool
     */
    private function permissionOrAccess(string $permission, User $user, Activity $activity) : bool
    {
        return $user->hasPermissionTo($permission) || self::isOwningUser($user, $activity);
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
        return $this->manage($user)
            && Activity::whereIn('role_id', $user->roles()->pluck('id'))
                ->orWhere('user_id', $user->id)
                ->count() > 0;
    }

    /**
     * Determine whether the user can view the activity.
     *
     * @param  User  $user
     * @param  Activity  $activity
     * @return bool
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
        return $user->hasPermissionTo('activity-create');
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
        return $this->permissionOrAccess('activity-update', $user, $activity);
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
        return $this->permissionOrAccess('activity-cancel', $user, $activity);
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

        // Delete is only allowed if there are no enrollments
        // yet.
        if ($activity->enrollments()->whereNull('deleted_at')->count() == 0) {
            return $this->permissionOrAccess('activity-delete', $user, $activity);
        }

        // Allow delete only if the permission is granted
        return $user->hasPermissionTo('activity-delete');
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
        return $this->permissionOrAccess('activity-delete', $user, $activity);
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
        // Don't allow deletion of activities that have payments within the last year
        if ($activity->payments()->where('updated_at', '>', today()->subYears(7))) {
            return false;
        }

        // Allow only if the permission is present
        return $user->hasPermissionTo('activity-purge');
    }

    /**
     * Determines whether the user can manage all activities, desipite the owner
     *
     * @param User $user
     * @return bool
     */
    public function manage(User $user)
    {
        return $user->hasAnyPermission([
            'activity-create',
            'activity-update',
            'activity-cancel',
            'activity-delete'
        ]);
    }
}
