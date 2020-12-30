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

/**
 * Updates all non-cancelled enrollments' user_type to the correct type.
 * Should run on a schedule
 *
 * @package App\Jobs
 */
class UpdateEnrollmentUserTypes implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Get all enrollments, with user, as cursor
        $cursor = Enrollment::query()
            ->whereNotState('state', Cancelled::class)
            ->with('user', 'user.roles')
            ->cursor();

        // Keep a count
        $parsed = 0;
        $updated = 0;

        // Use a cursor (read one item at a time)
        foreach ($cursor as $enrollment) {
            $intendedState = $enrollment->user->is_member ? Enrollment::USER_TYPE_MEMBER : Enrollment::USER_TYPE_GUEST;

            // Update counts
            $parsed++;

            logger()->debug('Checking {enrollment}, making sure {current-type} matches {intended-type}.', [
                'enrollment' => $enrollment,
                'current-type' => $intendedState,
                'intended-type' => $enrollment->user_type,
            ]);

            // Skip if good
            if ($intendedState === $enrollment->user_type) {
                continue;
            }

            // Debug
            logger()->debug('Updating {enrollment}, since types don\'t match', compact('enrollment'));

            // Update if different and only set that column
            $enrollment->user_type = $intendedState;
            $enrollment->save(['user_type']);

            // Update counts
            $updated++;
        }

        // Log change
        logger()->info('Updated {updated-count} out of {parsed-count} enrollments.', [
            'updated-count' => $updated,
            'parsed-count' => $parsed,
        ]);
    }
}
