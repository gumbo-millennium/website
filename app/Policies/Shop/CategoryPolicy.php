<?php

declare(strict_types=1);

namespace App\Policies\Shop;

use App\Models\Shop\Category;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CategoryPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any category policies.
     */
    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('shop-admin');
    }

    /**
     * Determine whether the user can view the category policy.
     */
    public function view(User $user, Category $categoryPolicy)
    {
        return $user->hasPermissionTo('shop-admin');
    }

    /**
     * Determine whether the user can create category policies.
     */
    public function create(User $user)
    {
        return $user->hasPermissionTo('shop-admin');
    }

    /**
     * Determine whether the user can update the category policy.
     */
    public function update(User $user, Category $categoryPolicy)
    {
        return $user->hasPermissionTo('shop-admin');
    }

    /**
     * Determine whether the user can delete the category policy.
     */
    public function delete(User $user, Category $categoryPolicy)
    {
        return $user->hasPermissionTo('shop-admin');
    }

    /**
     * Determine whether the user can restore the category policy.
     */
    public function restore(User $user, Category $categoryPolicy)
    {
        return $user->hasPermissionTo('shop-admin');
    }

    /**
     * Determine whether the user can permanently delete the category policy.
     */
    public function forceDelete(User $user, Category $categoryPolicy)
    {
        return $user->hasPermissionTo('shop-admin');
    }
}
