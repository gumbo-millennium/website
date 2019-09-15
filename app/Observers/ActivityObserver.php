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
        /*
            Normalize seats
        */
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

        /*
            Normalize prices
        */
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

        /*
            Normalize enrollment dates
        */
        $notOpenFree = (
            $activity->seats !== null ||
            $activity->public_seats !== null ||
            $activity->price_member !== null ||
            $activity->price_guest !== null
        );

        // Cap enrollment end on the end date of the activity end
        if ($activity->enrollment_end > $activity->end_date) {
            $activity->enrollment_end = $activity->end_date;
        }

        // Ensure a start date exists if the event is not open and free
        if ($notOpenFree && $activity->enrollment_start === null) {
            $activity->enrollment_start = today('Europe/Amsterdam')->subDays(7);
        }

        // Ensure an end date is set if a start date is too
        if ($activity->enrollment_start !== null && $activity->enrollment_end === null) {
            $activity->enrollment_end = $activity->start_date;
        }
    }
}
