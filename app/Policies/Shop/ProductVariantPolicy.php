<?php

declare(strict_types=1);

namespace App\Policies\Shop;

use App\Models\Shop\ProductVariant;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductVariantPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any product variants.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('shop-admin');
    }

    /**
     * Determine whether the user can view the product variant.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Shop\ProductVariant  $productVariant
     * @return mixed
     */
    public function view(User $user, ProductVariant $productVariant)
    {
        return $user->hasPermissionTo('shop-admin');
    }

    /**
     * Determine whether the user can create product variants.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->hasPermissionTo('shop-admin');
    }

    /**
     * Determine whether the user can update the product variant.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Shop\ProductVariant  $productVariant
     * @return mixed
     */
    public function update(User $user, ProductVariant $productVariant)
    {
        return $user->hasPermissionTo('shop-admin');
    }

    /**
     * Determine whether the user can delete the product variant.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Shop\ProductVariant  $productVariant
     * @return mixed
     */
    public function delete(User $user, ProductVariant $productVariant)
    {
        return $user->hasPermissionTo('shop-admin');
    }

    /**
     * Determine whether the user can restore the product variant.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Shop\ProductVariant  $productVariant
     * @return mixed
     */
    public function restore(User $user, ProductVariant $productVariant)
    {
        return $user->hasPermissionTo('shop-admin');
    }

    /**
     * Determine whether the user can permanently delete the product variant.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Shop\ProductVariant  $productVariant
     * @return mixed
     */
    public function forceDelete(User $user, ProductVariant $productVariant)
    {
        return $user->hasPermissionTo('shop-admin');
    }
}
