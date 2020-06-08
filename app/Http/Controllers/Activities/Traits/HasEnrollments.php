<?php

declare(strict_types=1);

namespace App\Http\Controllers\Activities\Traits;

use App\Exceptions\EnrollmentNotFoundException;
use App\Models\Activity;
use App\Models\Enrollment;
use Illuminate\Http\Request;

/**
 * Redirects consistently for not-found resources
 */
trait HasEnrollments
{
    /**
     * Finds an active enrollment for the requesting user and activity.
     * @param Request $request
     * @param Activity $activity
     * @return App\Models\Enrollment
     * @throws EnrollmentNotFoundException
     */
    public function findActiveEnrollmentOrFail(Request $request, Activity $activity): Enrollment
    {
        // Perform query
        $enrollment = $this->findActiveEnrollment($request, $activity);

        // Redirect to activity page if there is no enrollment
        if ($enrollment === null) {
            // Add warning
            flash('Je bent niet ingeschreven voor deze activiteit')->warning();

            // Redirect to activity
            throw new EnrollmentNotFoundException('Je bent niet ingeschreven voor deze activiteit');
        }

        // Return the enrollment
        return $enrollment;
    }

    /**
     * Finds the enrollment for the user making the request
     * @param Request $request
     * @param Activity $activity
     * @return null|Enrollment
     */
    public function findActiveEnrollment(Request $request, Activity $activity): ?Enrollment
    {
        // Can't find one if not logged in
        if (!$request->user()) {
            return null;
        }

        // Perform query
        return Enrollment::findActive($request->user(), $activity);
    }
}
