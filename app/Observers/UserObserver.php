<?php

namespace App\Observers;

use App\Models\User;
use function Opis\Closure\serialize;
use App\Jobs\UpdateWordPressUserJob;

/**
 * Observes changes in users and mimics them to the User's Wordpress account.
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class UserObserver
{
    /**
     * Handle the user "created" event.
     *
     * @param  \App\App\User  $user
     * @return void
     */
    public function created(User $user)
    {
        UpdateWordPressUserJob::dispatch($user);
    }

    /**
     * Handle the user "updated" event.
     *
     * @param  \App\App\User  $user
     * @return void
     */
    public function updated(User $user)
    {
        UpdateWordPressUserJob::dispatch($user);
    }

    /**
     * Handle the user "deleted" event.
     *
     * @param  \App\App\User  $user
     * @return void
     */
    public function deleted(User $user)
    {
        UpdateWordPressUserJob::dispatch($user);
    }

    /**
     * Handle the user "restored" event.
     *
     * @param  \App\App\User  $user
     * @return void
     */
    public function restored(User $user)
    {
        UpdateWordPressUserJob::dispatch($user);
    }

    /**
     * Handle the user "force deleted" event.
     *
     * @param  \App\App\User  $user
     * @return void
     */
    public function forceDeleted(User $user)
    {
        //
    }
}
