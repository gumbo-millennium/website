<?php

declare(strict_types=1);

namespace App\Http\Controllers\Activities;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\States\Enrollment\Confirmed;
use App\Models\States\Enrollment\Paid;
use App\Models\States\Enrollment\Seeded;
use Illuminate\Http\Request;

/**
 * Handles redirecting the request internally to the
 * right controller.
 *
 * This is used instead of multiple routes, so we can use
 * the /activity/<slug>/enroll route for GET requests and some
 * POST requests.
 */
class TunnelController extends Controller
{
    /**
     * Handles get requests on the /activity/<activity>/enroll route.
     *
     * @return Illuminate\Http\RedirectResponse|Illuminate\Http\Response
     */
    public function get(Request $request, Activity $activity)
    {
        // Get enrollment
        $enrollment = Enrollment::findActive($request->user(), $activity);

        // Redirect to activity if no enrollment is present
        if (! $enrollment) {
            logger()->debug('Missing enrollent for {activity}, redirecting back', compact('activity'));

            return redirect()->route('activity.show', compact('activity'));
        }

        // Forward to form controller
        if ($enrollment->wanted_state instanceof Seeded) {
            logger()->debug('{enrollment} wants seeded, redirecting to form', compact('enrollment', 'activity'));

            return app()->call(FormController::class . '@show', compact('activity', 'enrollment'));
        }

        // Forward to payment controller
        if ($enrollment->wanted_state instanceof Paid) {
            logger()->debug('{enrollment} wants paid, redirecting to ideal', compact('enrollment', 'activity'));

            return app()->call(PaymentController::class . '@show', compact('activity', 'enrollment'));
        }

        // Enrollments needs a hug, allow it to
        if ($enrollment->wanted_state instanceof Confirmed) {
            // Confirm enrollment
            $enrollment->transitionTo($enrollment->wanted_state);
            $enrollment->save();

            // Redirect back to activity
            logger()->debug('{enrollment} confirmed, redirecting to activity', compact('enrollment', 'activity'));

            return redirect()->route('activity.show', compact('activity'));
        }

        // No idea what to do: return to activity
        logger()->debug('{enrollment} wants unknown {wanted}, redirecting back', [
            'enrollment' => $enrollment,
            'activity' => $activity,
            'wanted' => $enrollment->wanted_state,
        ]);

        return redirect()->route('activity.show', compact('activity'));
    }
}
