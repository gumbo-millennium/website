<?php

declare(strict_types=1);

namespace App\Policies\Shop;

use App\Models\Shop\Order;
use App\Models\Shop\ProductVariant;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any orders.
     */
    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('shop-admin');
    }

    /**
     * Determine whether the user can view the order.
     *
     * @param \App\Shop\Order $order
     */
    public function view(User $user, Order $order)
    {
        return $user->hasPermissionTo('shop-admin');
    }

    /**
     * Determine whether the user can create orders.
     */
    public function create(User $user)
    {
        return false;
    }

    /**
     * Determine whether the user can update the order.
     *
     * @param \App\Shop\Order $order
     */
    public function update(User $user, Order $order)
    {
        return $user->hasPermissionTo('shop-admin') && $order->shipped_at === null;
    }

    /**
     * Determine whether the user can delete the order.
     *
     * @param \App\Shop\Order $order
     */
    public function delete(User $user, Order $order)
    {
        return false;
    }

    /**
     * Determine whether the user can restore the order.
     *
     * @param \App\Shop\Order $order
     */
    public function restore(User $user, Order $order)
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the order.
     *
     * @param \App\Shop\Order $order
     */
    public function forceDelete(User $user, Order $order)
    {
        return false;
    }

    /**
     * Prevent the users from attaching new product variants.
     */
    public function attachAnyProductVariant(User $user, Order $order): bool
    {
        return false;
    }

    /**
     * Prevent the users from detaching product variants.
     */
    public function detachAnyProductVariant(User $user, Order $order): bool
    {
        return false;
    }
}
