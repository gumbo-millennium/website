<?php

declare(strict_types=1);

namespace Tests\Traits;

use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\States\Enrollment\Confirmed;
use App\Models\User;
use Illuminate\Support\Facades\Date;

/**
 * Cancels activities properly
 */
trait MutatesActivities
{
    /**
     * Fill up all remaining slots
     * @param Activity $activity
     * @return Activity
     */
    protected function sellOutActivity(Activity $activity): Activity
    {
        // Ensure we can sell out
        if ($activity->seats === null) {
            $activity->seats = 5;
            $activity->save();
        }

        // Create missing enrollments
        if ($activity->available_seats > 0) {
            // Create random users for each seat
            $users = \factory(User::class, $activity->available_seats)->create();

            // Iterate through users to create an enrollment each
            foreach ($users as $user) {
                // Sanity
                \assert($user instanceof User);

                // Create
                $enrollment = \factory(Enrollment::class, 1)->create([
                    'user_id' => $user,
                    'activity_id' => $activity->id,
                    'state' => Confirmed::$name
                ])->first();

                // Sanity
                \assert($enrollment instanceof Enrollment);
            }
        }

        // Reload activity
        $activity->refresh();

        // Done
        return $activity;
    }

    /**
     * Cancels the given activity
     * @param Activity $activity
     * @return Activity
     */
    protected function cancelActivity(Activity $activity): Activity
    {
        $activity->cancelled_at = Date::now();
        $activity->cancelled_reason = 'test';
        $activity->save();
        return $activity;
    }
}
