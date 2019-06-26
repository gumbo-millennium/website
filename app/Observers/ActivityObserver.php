<?php

namespace App\Observers;

use App\Models\Activity;

/**
 * Ensures values on the activity model are up to snuff.
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class ActivityObserver
{
    /**
     * Validates values of an Activity. Has a high complexity but isn't run too often
     *
     * @param  \App\App\Models\Activity  $activity
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function saving(Activity $activity)
    {
        // Make sure there's an enrollment start date
        if ($activity->enrollment_start === null) {
            $activity->enrollment_start = today('Europe/Amsterdam')->subDays(7);
        }

        // Make sure there's an enrollment end date which is not after the event end date
        if ($activity->enrollment_end === null || $activity->enrollment_end > $activity->event_end) {
            $activity->enrollment_end = $activity->event_end;
        }

        // Make sure the seats aren't below or equal to zero, it should be null
        if ($activity->seats !== null && $activity->seats <= 0) {
            $activity->seats = null;
        }

        // Ensure the same for guest seats
        if ($activity->guest_seats !== null && $activity->guest_seats <= 0) {
            $activity->guest_seats = null;
        }

        // Make sure the number of guest seats does not exceed the number of total seats
        if ($activity->guest_seats && $activity->seats && $activity->guest_seats > $activity->seats) {
            $activity->guest_seats = $activity->seats;
        }

        // Ensure ticket prices are null or positive
        if ($activity->price_member !== null && $activity->price_member <= 0) {
            $activity->price_member = null;
        }

        // Ensure guest ticket prices are null or positive
        if ($activity->price_guest !== null && $activity->price_guest <= 0) {
            $activity->price_guest = null;
        }

        // Ensure the ticket prices for guests are at or higher than the ticket prices for members
        if ($activity->price_member !== null) {
            if ($activity->price_guest !== null && $activity->price_guest < $activity->price) {
                $activity->price_guest = $activity->price;
            }
        }
    }
}
