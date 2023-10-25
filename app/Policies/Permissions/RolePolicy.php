<?php

declare(strict_types=1);

namespace App\Policies\Permissions;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use Spatie\Permission\Contracts\Role;

class RolePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any roles.
     */
    public function viewAny(User $user): Response|bool
    {
        return $user->hasPermissionTo('role');
    }

    /**
     * Determine whether the user can view the role.
     */
    public function view(User $user, Role $role): Response|bool
    {
        return $user->hasPermissionTo('role');
    }

    /**
     * Prevent the user to create roles.
     */
    public function create(User $user): Response|bool
    {
        return false;
    }

    /**
     * Prevent the user to update the role.
     */
    public function update(User $user, Role $role): Response|bool
    {
        return false;
    }

    /**
     * Prevent the user to delete the role.
     */
    public function delete(User $user, Role $role): Response|bool
    {
        return false;
    }

    /**
     * Determine whether the user can attach a user to the role.
     */
    public function attachUser(User $user): Response|bool
    {
        return $user->hasPermissionTo('role-admin');
    }

    /**
     * Determine whether the user can detach a user from the role.
     */
    public function detachUser(User $user): Response|bool
    {
        return $user->hasPermissionTo('role-admin');
    }

    /**
     * Prevent the user to attach a permission to the role.
     */
    public function attachPermission(): Response|bool
    {
        return false;
    }

    /**
     * Prevent the user to detach a permission from the role.
     */
    public function detachPermission(): Response|bool
    {
        return false;
    }
}
