<?php

declare(strict_types=1);

namespace App\Events\Public;

use App\Models\JoinSubmission;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a new user has signed up via the Join Gumbo form.
 */
class UserJoinedEvent implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public readonly JoinSubmission $joinSubmission)
    {
        // noop
    }
}
