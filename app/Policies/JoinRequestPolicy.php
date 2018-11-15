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
     * Checks if the user is allowed to manage join requests
     *
     * @param User $user
     * @return void
     */
    public function manage(User $user)
    {
        return $user->hasAnyPermission([
            'join.update',
            'join.accept',
            'join.decline',
            'join.delete'
        ]);
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
        return ($user->is($joinRequest->owner) || $user->hasPermissionTo('join.update'));
    }

    /**
     * Returns if the user is allowed to accept the given join request
     *
     * @param User $user
     * @param JoinRequest $joinRequest
     * @return bool
     */
    public function accept(User $user, JoinRequest $joinRequest) : bool
    {
        $perms = ['join.accept', 'join.update'];

        // You need the decline permission to un-decline a request
        if ($joinRequest->is_declined) {
            $perms[] = 'join.decline';
        }

        // Return if all permissions are granted
        return $user->hasAllPermissions($perms);
    }

    /**
     * Returns if the user is allowed to decline the given join request
     *
     * @param User $user
     * @param JoinRequest $joinRequest
     * @return bool
     */
    public function decline(User $user, JoinRequest $joinRequest) : bool
    {
        $perms = ['join.decline', 'join.update'];

        // You need the accept permission to un-accept a request
        if ($joinRequest->is_accepted) {
            $perms[] = 'join.accept';
        }

        // Return if all permissions are granted
        return $user->hasAllPermissions($perms);
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
        return $user->is($joinRequest->owner) || $user->hasPermissionTo('join.delete');
    }
}
