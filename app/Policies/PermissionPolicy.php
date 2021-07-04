<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Spatie\Permission\Models\Permission;

/**
 * Handles editing permissions.
 */
class PermissionPolicy
{
    use HandlesAuthorization;

    public const ADMIN_PERMISSION = 'role-admin';

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        return $user->can('manage', User::class);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Permission $permission)
    {
        return $user->can('manage', $permission);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Permission $permission)
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Permission $permission)
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Permission $permission)
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Permission $permission)
    {
        return false;
    }

    /**
     * Allow attaching users if admin or if granted
     * management access.
     *
     * @return bool
     */
    public function attachUser(User $user, Permission $permission)
    {
        return $user->can('manage', $permission);
    }

    /**
     * Allow attaching users if admin or if granted
     * management access.
     *
     * @return bool
     */
    public function detachUser(User $user, Permission $permission)
    {
        return $user->can('manage', $permission);
    }

    /**
     * Can the given user manage permissions.
     */
    public function manage(User $user): bool
    {
        return $user->can('admin', Role::class);
    }
}
