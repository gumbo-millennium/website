<?php

namespace App\Policies;

use App\Models\User;
use App\Models\JoinSubmission;
use Illuminate\Auth\Access\HandlesAuthorization;

class JoinSubmissionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any join submissions.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('join-manage');
    }

    /**
     * Determine whether the user can view the join submission.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\JoinSubmission  $joinSubmission
     * @return mixed
     */
    public function view(User $user, JoinSubmission $joinSubmission)
    {
        return $user->hasPermissionTo('join-manage')
            || ($joinSubmission->email === $user->email && $user->hasVerifiedEmail());
    }

    /**
     * Determine whether the user can create join submissions.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        // Anyone can create join submissions
        return true;
    }

    /**
     * Determine whether the user can update the join submission.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\JoinSubmission  $joinSubmission
     * @return mixed
     */
    public function update(User $user, JoinSubmission $joinSubmission)
    {
        return $user->hasPermissionTo('join-manage');
    }

    /**
     * Determine whether the user can delete the join submission.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\JoinSubmission  $joinSubmission
     * @return mixed
     */
    public function delete(User $user, JoinSubmission $joinSubmission)
    {
        return $user->hasPermissionTo('join-manage');
    }
}
