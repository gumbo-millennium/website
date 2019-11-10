<?php

namespace App\Observers;

use App\Jobs\Stripe\CustomerUpdateJob;
use App\Models\User;

class UserObserver
{
    /**
     * Handle the user "created" event.
     *
     * @param  User  $user
     * @return void
     */
    public function created(User $user)
    {
        dispatch(new CustomerUpdateJob($user));
    }

    /**
     * Handle the user "updated" event.
     *
     * @param  User  $user
     * @return void
     */
    public function updated(User $user)
    {
        // Trigger update if any name was changed, or the email address
        if ($user->wasChanged(['first_name', 'insert', 'last_name', 'email'])) {
            dispatch(new CustomerUpdateJob($user));
        }
    }
}
