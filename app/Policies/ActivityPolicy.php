<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Activity;
use Illuminate\Auth\Access\HandlesAuthorization;
use Spatie\Permission\Models\Role;

class ActivityPolicy
{
    use HandlesAuthorization;

    public function canTouch(User $user, Activity $activity)
    {
        // Allow if a wildcard is present
        if ($user->hasPermissionTo('event-manage-all')) {
            return true;
        }

        // Allow if the user can edit and the event is missing a role
        if ($user->hasPermissionTo('event-edit') && $activity->role === null) {
            return true;
        }

        // Add if the user has the given role
        return $user->hasRole($activity->role);
    }

    /**
     * Allow the user to view any event in the admin
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user)
    {
        return $user->hasAnyPermission('event-add', 'event-manage-all');
    }

    /**
     * Determine whether the user can view the activity.
     *
     * @param  \App\User  $user
     * @param  \App\Activity  $activity
     * @return bool
     */
    public function view(User $user, Activity $activity)
    {
        return $this->canTouch($user, $activity);
    }

    /**
     * Determine whether the user can create activities.
     *
     * @param  \App\User  $user
     * @return bool
     */
    public function create(User $user)
    {
        return $user->hasAnyPermission('event--manage-all', 'event-add');
    }

    /**
     * Determine whether the user can update the activity.
     *
     * @param  \App\User  $user
     * @param  \App\Activity  $activity
     * @return bool
     */
    public function update(User $user, Activity $activity)
    {
        return $this->canTouch($user, $activity);
    }

    /**
     * Determine whether the user can delete the activity.
     *
     * @param  \App\User  $user
     * @param  \App\Activity  $activity
     * @return bool
     */
    public function delete(User $user, Activity $activity)
    {
        return $this->canTouch($user, $activity);
    }

    /**
     * Determine whether the user can restore the activity.
     *
     * @param  \App\User  $user
     * @param  \App\Activity  $activity
     * @return bool
     */
    public function restore(User $user, Activity $activity)
    {
        return $this->canTouch($user, $activity);
    }

    /**
     * Determine whether the user can permanently delete the activity.
     *
     * @param  \App\User  $user
     * @param  \App\Activity  $activity
     * @return bool
     */
    public function forceDelete(User $user, Activity $activity)
    {
        return $this->canTouch($user, $activity);
    }
}
