<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\JoinSubmission;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Allow wildcard editing of join requests
 */
class JoinSubmissionPolicy
{
    // phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter
    use HandlesAuthorization;

    public const ADMIN_PERMISSION = 'join-admin';

    /**
     * Determine whether the user can view any join submissions.
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $user->can('manage', JoinSubmission::class);
    }

    /**
     * Determine whether the user can view the join submission.
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function view(User $user)
    {
        return $user->can('manage', JoinSubmission::class);
    }

    /**
     * Determine whether the user can create join submissions.
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->can('manage', JoinSubmission::class);
    }

    /**
     * Determine whether the user can update the join submission.
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function update(User $user)
    {
        return $user->can('manage', JoinSubmission::class);
    }

    /**
     * Determine whether the user can delete the join submission.
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function delete(User $user)
    {
        return $user->can('manage', JoinSubmission::class);
    }

    /**
     * Determine whether the user can manage join submission.
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function manage(User $user)
    {
        return $user->hasPermissionTo(self::ADMIN_PERMISSION);
    }
}
