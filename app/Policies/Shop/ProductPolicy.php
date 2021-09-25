<?php

declare(strict_types=1);

namespace App\Policies\Shop;

use App\Models\Shop\Product;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any products.
     */
    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('shop-admin');
    }

    /**
     * Determine whether the user can view the product.
     *
     * @param \App\Shop\Product $product
     */
    public function view(User $user, Product $product)
    {
        return $user->hasPermissionTo('shop-admin');
    }

    /**
     * Determine whether the user can create products.
     */
    public function create(User $user)
    {
        return $user->hasPermissionTo('shop-admin');
    }

    /**
     * Determine whether the user can update the product.
     *
     * @param \App\Shop\Product $product
     */
    public function update(User $user, Product $product)
    {
        return $user->hasPermissionTo('shop-admin');
    }

    /**
     * Determine whether the user can delete the product.
     *
     * @param \App\Shop\Product $product
     */
    public function delete(User $user, Product $product)
    {
        return $user->hasPermissionTo('shop-admin');
    }

    /**
     * Determine whether the user can restore the product.
     *
     * @param \App\Shop\Product $product
     */
    public function restore(User $user, Product $product)
    {
        return $user->hasPermissionTo('shop-admin');
    }

    /**
     * Determine whether the user can permanently delete the product.
     *
     * @param \App\Shop\Product $product
     */
    public function forceDelete(User $user, Product $product)
    {
        return false;
    }
}
