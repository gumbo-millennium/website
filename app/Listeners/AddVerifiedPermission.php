<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Auth\Events\Verified as LaravelVerifiedEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Listens to verifications of e-mail accounts
 * Adds users to 'verified' when they have.
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class AddVerifiedPermission
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  LaravelVerifiedEvent  $event
     * @return void
     */
    public function handle(LaravelVerifiedEvent $event)
    {
        // Get verified user
        $user = $event->user;

        // Add role to user if not yet present
        if ($user instanceof User && !$user->hasRole('verified')) {
            $user->assignRole('verified');
        }
    }
}
