<?php

declare(strict_types=1);

namespace App\Policies\Shop;

use App\Models\Shop\Order;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any orders.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('shop-admin');
    }

    /**
     * Determine whether the user can view the order.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Shop\Order  $order
     * @return mixed
     */
    public function view(User $user, Order $order)
    {
        return $user->hasPermissionTo('shop-admin');
    }

    /**
     * Determine whether the user can create orders.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return false;
    }

    /**
     * Determine whether the user can update the order.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Shop\Order  $order
     * @return mixed
     */
    public function update(User $user, Order $order)
    {
        return $user->hasPermissionTo('shop-admin') && $order->shipped_at === null;
    }

    /**
     * Determine whether the user can delete the order.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Shop\Order  $order
     * @return mixed
     */
    public function delete(User $user, Order $order)
    {
        return false;
    }

    /**
     * Determine whether the user can restore the order.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Shop\Order  $order
     * @return mixed
     */
    public function restore(User $user, Order $order)
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the order.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Shop\Order  $order
     * @return mixed
     */
    public function forceDelete(User $user, Order $order)
    {
        return false;
    }
}
