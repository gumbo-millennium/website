<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Spatie\Permission\Models\Permission;

/**
 * Handles editing permissions
 */
class PermissionPolicy
{
    use HandlesAuthorization;

    /**
     * @var string Permission name
     */
    public const ADMIN_PERMISSION = 'role-admin';

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $user->can('manage', User::class);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Permission  $permission
     * @return mixed
     */
    public function view(User $user, Permission $permission)
    {
        return $user->can('manage', $permission);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->can('manage', Permission::class);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Permission  $permission
     * @return mixed
     */
    public function update(User $user, Permission $permission)
    {
        return $user->can('manage', $permission);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Permission  $permission
     * @return mixed
     */
    public function delete(User $user, Permission $permission)
    {
        return $user->can('manage', $permission);
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Permission  $permission
     * @return mixed
     */
    public function restore(User $user, Permission $permission)
    {
        return $user->can('manage', $permission);
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Permission  $permission
     * @return mixed
     */
    public function forceDelete(User $user, Permission $permission)
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
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function attachUser(User $user, Permission $permission, User $model)
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
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function detachUser(User $user, Permission $permission, User $model)
    {
        return $user->can('manage', $permission);
    }

    /**
     * Can the given user manage permissions
     *
     * @param User $user
     * @param Permission|null $permission Role that's being managed
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function manage(User $user, Permission $permission = null): bool
    {
        return $user->can('admin', Role::class);
    }
}
