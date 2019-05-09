<?php

namespace App\Policies;

use App\User;
use App\JoinSubmission;
use Illuminate\Auth\Access\HandlesAuthorization;

class JoinSubmissionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the join submission.
     *
     * @param  \App\User  $user
     * @param  \App\JoinSubmission  $joinSubmission
     * @return mixed
     */
    public function view(User $user, JoinSubmission $joinSubmission)
    {
        return $user->hasPermissionTo('join-manage') || $user->email === $joinSubmission->email;
    }

    /**
     * Determine whether the user can update the join submission.
     *
     * @param  \App\User  $user
     * @param  \App\JoinSubmission  $joinSubmission
     * @return bool
     */
    public function update(User $user, JoinSubmission $joinSubmission)
    {
        return $user->hasPermissionTo('join-manage');
    }

    /**
     * Determine whether the user can delete the join submission.
     *
     * @param  \App\User  $user
     * @param  \App\JoinSubmission  $joinSubmission
     * @return bool
     */
    public function delete(User $user, JoinSubmission $joinSubmission)
    {
        return $user->hasPermissionTo('join-manage');
    }
}
