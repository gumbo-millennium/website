<?php

namespace App\Observers;

use App\Jobs\Stripe\UpdateCouponJob;
use App\Models\Activity;
use Illuminate\Support\Str;

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

        /*
            Normalize prices
        */
        // Ensure ticket prices are null or positive
        if ($activity->member_discount !== null && $activity->member_discount <= 0) {
            $activity->member_discount = null;
        }

        // Ensure guest ticket prices are null or positive
        if ($activity->price !== null && $activity->price <= 0) {
            $activity->price = null;
        }

        // Ensure the ticket prices for guests are at or higher than the ticket prices for members
        if ($activity->member_discount !== null) {
            if ($activity->price !== null && $activity->member_discount > $activity->price) {
                $activity->member_discount = $activity->price;
            }
        }

        /*
            Normalize enrollment dates
        */
        $notOpenFree = $activity->seats !== null || !$activity->is_free;

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

        // Make sure statements are valid statements (ASCII uppercase)
        if ($activity->statement !== null) {
            $activity->statement = Str::limit(Str::ascii($activity->statement), 16, '');
        }
    }

    /**
     * Update the coupon
     * @param Activity $activity
     * @return void
     */
    public function saved(Activity $activity)
    {
        UpdateCouponJob::dispatch($activity);
    }
}
