<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Spatie\Permission\Models\Role;

/**
 * Handle Role modifications.
 */
class RolePolicy
{
    // phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter
    use HandlesAuthorization;

    public const ADMIN_PERMISSION = 'role-admin';

    public const USER_PERMISSION = 'role';

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        return $user->can('manage', User::class);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param \App\Models\Role $role
     */
    public function view(User $user, Role $role)
    {
        return $user->can('admin', $role);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        return $user->can('admin', Role::class);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param \App\Models\Role $role
     */
    public function update(User $user, Role $role)
    {
        return $user->can('admin', $role);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param \App\Models\Role $role
     */
    public function delete(User $user, Role $role)
    {
        return $user->can('admin', $role);
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param \App\Models\Role $role
     */
    public function restore(User $user, Role $role)
    {
        return $user->can('admin', $role);
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param \App\Models\Role $role
     */
    public function forceDelete(User $user, Role $role)
    {
        return $user->can('admin', $role);
    }

    /**
     * Allow attaching permissions if admin.
     *
     * @return bool
     */
    public function attachPermission(User $user, Role $role, Permission $permission)
    {
        return $user->can('admin', $role);
    }

    /**
     * Allow attaching users if admin or if granted
     * management access.
     *
     * @return bool
     */
    public function attachUser(User $user, Role $role, User $model)
    {
        return $user->can('admin', $role);
    }

    /**
     * Allow attaching users if admin or if granted
     * management access.
     *
     * @return bool
     */
    public function detachUser(User $user, Role $role, User $model)
    {
        return $user->can('admin', $role);
    }

    /**
     * Can the given user admin all roles.
     */
    public function admin(User $user): bool
    {
        return $user->hasPermissionTo(self::ADMIN_PERMISSION);
    }
}
