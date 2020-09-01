<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Enrollment;
use App\Models\States\Enrollment\Cancelled;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PruneExpiredEnrollments implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Execute the job.
     * @return void
     */
    public function handle()
    {
        // Get all enrollments still unconfirmed
        $enrollments = Enrollment::query()
            ->with('user')
            ->whereNotNull('expire')
            ->where('expire', '<', now())
            ->whereNotState('state', Cancelled::class)
            ->cursor();

        foreach ($enrollments as $enrollment) {
            \assert($enrollment instanceof Enrollment);

            // Check if state is stable
            if ($enrollment->state->isStable()) {
                continue;
            }

            // Cancel enrollment
            $enrollment->state->transitionTo(Cancelled::class);
            $enrollment->deleted_reason = 'timeout';

            // Save changes
            $enrollment->save();
        }
    }
}
