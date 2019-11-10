<?php

declare(strict_types=1);

namespace App\Http\Controllers\Activities\Traits;

use App\Models\Activity;
use App\Models\Enrollment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Redirects consistently for not-found resources
 */
trait ConsistentRedirects
{
    /**
     * Finds an active enrollment for the requesting user and activity, or returns
     * a RedirectResponse with flashed data.
     *
     * @param Request $request
     * @param Activity $activity
     * @return Enrollment|RedirectResponse
     */
    public function findActiveEnrollmentOrRedirect(Request $request, Activity $activity)
    {
        $enrollment = Enrollment::findActive($request->user(), $activity);

        // Redirect to activity page if there is no enrollment
        if ($enrollment === null) {
            // Add warning
            flash('Je bent niet ingeschreven voor deze activiteit', 'warning');

            // Redirect to activity
            return redirect()->route('activity.show', compact('activity'));
        }

        // Return the enrollment
        return $enrollment;
    }
}
