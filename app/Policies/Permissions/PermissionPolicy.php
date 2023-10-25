<?php

declare(strict_types=1);

namespace App\Policies\Permissions;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use Spatie\Permission\Models\Permission;

class PermissionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any permissions.
     */
    public function viewAny(User $user): Response|bool
    {
        return $user->hasPermissionTo('role');
    }

    /**
     * Determine whether the user can view the permission.
     */
    public function view(User $user, Permission $permission): Response|bool
    {
        return $user->hasPermissionTo('role');
    }

    /**
     * Prevent the user to create permissions.
     */
    public function create(User $user): Response|bool
    {
        return false;
    }

    /**
     * Prevent the user to update the permission.
     */
    public function update(User $user, Permission $permission): Response|bool
    {
        return false;
    }

    /**
     * Prevent the user to delete the permission.
     */
    public function delete(User $user, Permission $permission): Response|bool
    {
        return false;
    }

    /**
     * Prevent the user to restore the permission.
     */
    public function restore(User $user, Permission $permission): Response|bool
    {
        return false;
    }

    /**
     * Prevent the user to permanently delete the permission.
     */
    public function forceDelete(User $user, Permission $permission): Response|bool
    {
        return false;
    }

    /**
     * Prevent the user to attach a user to the permission.
     */
    public function attachUser(): Response|bool
    {
        return false;
    }

    /**
     * Prevent the user to detach a user from the permission.
     */
    public function detachUser(): Response|bool
    {
        return false;
    }

    /**
     * Prevent the user to attach a role to the permission.
     */
    public function attachRole(): Response|bool
    {
        return false;
    }

    /**
     * Prevent the user to detach a role from the permission.
     */
    public function detachRole(): Response|bool
    {
        return false;
    }
}
