<?php

namespace App\Observers;

use App\Jobs\Stripe\CustomerUpdateJob;
use App\Models\User;

class UserObserver
{
    /**
     * Handle any kind of changes to the user
     * @param User $user
     * @return void
     */
    public function saved(User $user): void
    {
        // Don't act on console commands (speed up CLI commands)
        if (\app()->runningInConsole()) {
            return;
        }

        // Trigger update if the user was created, or if the name or email address was changed
        if ($user->wasRecentlyCreated || $user->wasChanged(['first_name', 'insert', 'last_name', 'email'])) {
            // Create or update the customer on Stripe
            dispatch(new CustomerUpdateJob($user));
        }
    }
}
