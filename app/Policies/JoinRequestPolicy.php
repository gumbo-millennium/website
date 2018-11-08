<?php

namespace App\Policies;

use App\User;
use App\JoinRequest;
use Illuminate\Auth\Access\HandlesAuthorization;

class JoinRequestPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the join request.
     *
     * @param  \App\User  $user
     * @param  \App\JoinRequest  $joinRequest
     * @return mixed
     */
    public function view(User $user, JoinRequest $joinRequest)
    {
        return (!$user->hasRole('member') && $user->join_request === null);
    }

    /**
     * Determine whether the user can create join requests.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return (!$user->hasRole('member') && $user->join_request === null);
    }

    /**
     * Determine whether the user can update the join request.
     *
     * @param  \App\User  $user
     * @param  \App\JoinRequest  $joinRequest
     * @return mixed
     */
    public function update(User $user, JoinRequest $joinRequest)
    {
        return ($user->is($joinRequest->owner) || $user->hasPermissionTo('join-request-manage'));
    }

    /**
     * Determine whether the user can delete the join request.
     *
     * @param  \App\User  $user
     * @param  \App\JoinRequest  $joinRequest
     * @return mixed
     */
    public function delete(User $user, JoinRequest $joinRequest)
    {
        return ($user->is(joinRequest->owner)) || $user->hasPermissionTo('join-request-manage');
    }
}
