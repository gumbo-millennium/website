<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ActivityMessage;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ActivityMessagePolicy
{
    use HandlesAuthorization;

    /**
     * Restrict monitoring all messages to activity admins.
     *
     * @return bool
     */
    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('activity-admin');
    }

    /**
     * Determine whether the user can view the activity message.
     *
     * @param \App\ActivityMessage $activityMessage
     * @return bool
     */
    public function view(User $user, ActivityMessage $activityMessage)
    {
        return $user->can('update', $activityMessage->activity);
    }

    /**
     * Prevent creating new activity messages directly. Actions should be used.
     *
     * @return bool
     */
    public function create(User $user)
    {
        return false;
    }

    /**
     * Activity messages are read-only, so no changes are allowed.
     *
     * @param \App\ActivityMessage $activityMessage
     * @return bool
     */
    public function update(User $user, ActivityMessage $activityMessage)
    {
        return false;
    }

    /**
     * Unsent activity messages may be deleted, to prevent sending them.
     *
     * @param \App\ActivityMessage $activityMessage
     * @return bool
     */
    public function delete(User $user, ActivityMessage $activityMessage)
    {
        return $user->can('view', $activityMessage) && $activityMessage->sent_at === null;
    }

    /**
     * Determine whether the user can restore the activity message.
     *
     * @param \App\ActivityMessage $activityMessage
     * @return bool
     */
    public function restore(User $user, ActivityMessage $activityMessage)
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the activity message.
     *
     * @param \App\ActivityMessage $activityMessage
     * @return bool
     */
    public function forceDelete(User $user, ActivityMessage $activityMessage)
    {
        return false;
    }
}
