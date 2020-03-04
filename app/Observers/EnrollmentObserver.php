<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Enrollment;
use App\Models\States\Enrollment\State as EnrollmentState;

/**
 * Listens for changes in enrollment elements. Sends users mails when they're
 * enrolled and unenrolled for events.
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class EnrollmentObserver
{
    /**
     * Ensure an expire date is present if required
     * @param Enrollment $enrollment
     * @return void
     */
    public function saving(Enrollment $enrollment): void
    {
        // Post-start enrollments don't expire
        if ($enrollment->activity->start_date < now()) {
            $enrollment->expire = null;
            return;
        }

        $isStable = $enrollment->state instanceof EnrollmentState && $enrollment->state->isStable();

        // Keep it simple if the enrollment is stable
        if ($isStable) {
            // Unset enrollment if we need to
            if ($enrollment->expire !== null) {
                $enrollment->expire = null;
            }

            // Stop check
            return;
        }

        // An expire date is already set, don't modify it.
        if ($enrollment->expire !== null) {
            return;
        }

        $activity = $enrollment->activity;

        // Get an hour before the start of the activity
        $startDate = (clone $activity->start_date)->subHour();

        // If the activity starts within 1 hour, stretch it up to 1 hour.
        $minExpireDate = now()->addHour(1);
        if ($startDate < $minExpireDate) {
            $enrollment->expire = $minExpireDate;
            return;
        }

        // Enrollments expire in a week.
        $maxExpireDate = now()->addWeek();
        if ($startDate > $maxExpireDate) {
            $enrollment->expire = $maxExpireDate;
            return;
        }

        // The event starts in less than a week but more than an hour, so we
        // just assign the (start date - 1hr) as expire date.
        $enrollment->expire = $startDate;
    }
}
