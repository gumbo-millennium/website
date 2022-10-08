<?php

declare(strict_types=1);

namespace App\Policies\GoogleWallet;

use App\Models\GoogleWallet\EventClass;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EventClassPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('devops');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function view(User $user, EventClass $walletClass)
    {
        //
        return $user->hasPermissionTo('devops');
    }

    /**
     * Determine whether the user can create models.
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function create(User $user)
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function update(User $user, EventClass $walletClass)
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function delete(User $user, EventClass $walletClass)
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function restore(User $user, EventClass $walletClass)
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function forceDelete(User $user, EventClass $walletClass)
    {
        return false;
    }
}
