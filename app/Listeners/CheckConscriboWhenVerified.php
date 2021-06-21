<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Jobs\UpdateConscriboUserJob;
use App\Models\User;
use Illuminate\Auth\Events\Verified;

/**
 * Update roles from Conscribo when the user has verified it's email.
 */
class CheckConscriboWhenVerified
{
    /**
     * Handle the event.
     */
    public function handle(Verified $event): void
    {
        // Asset user is of the right type
        assert($event->user instanceof User);

        // Log
        logger()->info('Validating {user}', ['user' => $event->user]);

        // Start job
        UpdateConscriboUserJob::dispatch($event->user);
    }
}
