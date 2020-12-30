<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Spatie\Permission\Models\Permission;

/**
 * Handles editing permissions
 */
class PermissionPolicy
{
    // phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter
    use HandlesAuthorization;

    public const ADMIN_PERMISSION = 'role-admin';

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $user->can('manage', User::class);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param Permission $permission
     * @return mixed
     */
    public function view(User $user, Permission $permission)
    {
        return $user->can('manage', $permission);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     * @return mixed
     */
    public function create(User $user)
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Permission $permission
     * @return mixed
     */
    public function update(User $user, Permission $permission)
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Permission $permission
     * @return mixed
     */
    public function delete(User $user, Permission $permission)
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param Permission $permission
     * @return mixed
     */
    public function restore(User $user, Permission $permission)
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param Permission $permission
     * @return mixed
     */
    public function forceDelete(User $user, Permission $permission)
    {
        return false;
    }

    /**
     * Allow attaching users if admin or if granted
     * management access
     *
     * @param User $user
     * @param Permission $permission
     * @return bool
     */
    public function attachUser(User $user, Permission $permission)
    {
        return $user->can('manage', $permission);
    }

    /**
     * Allow attaching users if admin or if granted
     * management access
     *
     * @param User $user
     * @param Permission $permission
     * @return bool
     */
    public function detachUser(User $user, Permission $permission)
    {
        return $user->can('manage', $permission);
    }

    /**
     * Can the given user manage permissions
     *
     * @param User $user
     * @return bool
     */
    public function manage(User $user): bool
    {
        return $user->can('admin', Role::class);
    }
}
