<?php

namespace App\Policies;

use App\Models\User;
use App\Models\JoinSubmission;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Allow wildcard editing of join requests
 */
class JoinSubmissionPolicy
{
    use HandlesAuthorization;

    /**
     * @var string Permission name
     */
    public const ADMIN_PERMISSION = 'join-admin';

    /**
     * Determine whether the user can view any join submissions.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $user->can('manage', JoinSubmission::class);
    }

    /**
     * Determine whether the user can view the join submission.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\JoinSubmission  $joinSubmission
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function view(User $user, JoinSubmission $joinSubmission)
    {
        return $user->can('manage', JoinSubmission::class);
    }

    /**
     * Determine whether the user can create join submissions.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->can('manage', JoinSubmission::class);
    }

    /**
     * Determine whether the user can update the join submission.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\JoinSubmission  $joinSubmission
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function update(User $user, JoinSubmission $joinSubmission)
    {
        return $user->can('manage', JoinSubmission::class);
    }

    /**
     * Determine whether the user can delete the join submission.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\JoinSubmission  $joinSubmission
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function delete(User $user, JoinSubmission $joinSubmission)
    {
        return $user->can('manage', JoinSubmission::class);
    }

    /**
     * Determine whether the user can manage join submission.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function manage(User $user)
    {
        return $user->hasPermissionTo(self::ADMIN_PERMISSION);
    }
}
