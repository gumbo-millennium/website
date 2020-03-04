<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Spatie\Permission\Models\Role;

/**
 * Handle Role modifications
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class RolePolicy
{
    // phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter
    use HandlesAuthorization;

    public const ADMIN_PERMISSION = 'role-admin';
    public const USER_PERMISSION = 'role';

    /**
     * Determine whether the user can view any models.
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $user->can('manage', User::class);
    }

    /**
     * Determine whether the user can view the model.
     * @param  \App\Models\User  $user
     * @param  \App\Models\Role  $role
     * @return mixed
     */
    public function view(User $user, Role $role)
    {
        return $user->can('admin', $role);
    }

    /**
     * Determine whether the user can create models.
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->can('admin', Role::class);
    }

    /**
     * Determine whether the user can update the model.
     * @param  \App\Models\User  $user
     * @param  \App\Models\Role  $role
     * @return mixed
     */
    public function update(User $user, Role $role)
    {
        return $user->can('admin', $role);
    }

    /**
     * Determine whether the user can delete the model.
     * @param  \App\Models\User  $user
     * @param  \App\Models\Role  $role
     * @return mixed
     */
    public function delete(User $user, Role $role)
    {
        return $user->can('admin', $role);
    }

    /**
     * Determine whether the user can restore the model.
     * @param  \App\Models\User  $user
     * @param  \App\Models\Role  $role
     * @return mixed
     */
    public function restore(User $user, Role $role)
    {
        return $user->can('admin', $role);
    }

    /**
     * Determine whether the user can permanently delete the model.
     * @param  \App\Models\User  $user
     * @param  \App\Models\Role  $role
     * @return mixed
     */
    public function forceDelete(User $user, Role $role)
    {
        return $user->can('admin', $role);
    }

    /**
     * Allow attaching permissions if admin
     * @param User $user
     * @param Permission $permission
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function attachPermission(User $user, Role $role, Permission $permission)
    {
        return $user->can('admin', $role);
    }

    /**
     * Allow attaching users if admin or if granted
     * management access
     * @param User $user
     * @param Permission $permission
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function attachUser(User $user, Role $role, User $model)
    {
        return $user->can('admin', $role);
    }

    /**
     * Allow attaching users if admin or if granted
     * management access
     * @param User $user
     * @param Permission $permission
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function detachUser(User $user, Role $role, User $model)
    {
        return $user->can('admin', $role);
    }

    /**
     * Can the given user admin all roles
     * @param User $user
     * @return bool
     */
    public function admin(User $user): bool
    {
        return $user->hasPermissionTo(self::ADMIN_PERMISSION);
    }
}
