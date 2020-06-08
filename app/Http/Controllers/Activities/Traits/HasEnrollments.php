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
        $enrollment = Enrollment::findActive($request->user(), $activity);

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
}
