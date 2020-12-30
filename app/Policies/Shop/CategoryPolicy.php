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
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('shop-admin');
    }

    /**
     * Determine whether the user can view the category policy.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Shop\Category  $categoryPolicy
     * @return mixed
     */
    public function view(User $user, Category $categoryPolicy)
    {
        return $user->hasPermissionTo('shop-admin');
    }

    /**
     * Determine whether the user can create category policies.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->hasPermissionTo('shop-admin');
    }

    /**
     * Determine whether the user can update the category policy.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Shop\Category  $categoryPolicy
     * @return mixed
     */
    public function update(User $user, Category $categoryPolicy)
    {
        return $user->hasPermissionTo('shop-admin');
    }

    /**
     * Determine whether the user can delete the category policy.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Shop\Category  $categoryPolicy
     * @return mixed
     */
    public function delete(User $user, Category $categoryPolicy)
    {
        return $user->hasPermissionTo('shop-admin');
    }

    /**
     * Determine whether the user can restore the category policy.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Shop\Category  $categoryPolicy
     * @return mixed
     */
    public function restore(User $user, Category $categoryPolicy)
    {
        return $user->hasPermissionTo('shop-admin');
    }

    /**
     * Determine whether the user can permanently delete the category policy.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Shop\Category  $categoryPolicy
     * @return mixed
     */
    public function forceDelete(User $user, Category $categoryPolicy)
    {
        return $user->hasPermissionTo('shop-admin');
    }
}
