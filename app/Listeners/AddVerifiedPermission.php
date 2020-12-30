<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Models\User;
use Illuminate\Auth\Events\Verified as LaravelVerifiedEvent;

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
        if (!($user instanceof User) || $user->hasRole('verified')) {
            return;
        }

        $user->assignRole('verified');
    }
}
