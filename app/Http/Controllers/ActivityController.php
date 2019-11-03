<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * User routes for the activities
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class ActivityController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        // Get all future events by default
        $query = Activity::where('end_date', '>', now());

        // Get all past events instead
        if ($request->has('past')) {
            $query = Activity::where('end_date', '<=', now());
        }

        $activities = $query->paginate();

        // Collect an empty list of enrollments
        $enrollments = collect();

        if ($request->user) {
            // Get all user enrollments, indexed by the activity_id
            $enrollments = Enrollment::where([
                'user_id' => $request->user->id
            ])->orderBy('created_at', 'asc')->get()->keyBy('activity_id');
        }

        // Render the view with the events and their enrollments
        return view('activities.index', [
            'activities' => $activities,
            'enrollments' => $enrollments,
            'past' => $request->has('past')
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  Activity  $activity
     * @return Response
     */
    public function show(Request $request, Activity $activity)
    {
        // Load enrollments
        $activity->load(['enrollments']);


        // Get request user
        $user = $request->user();

        // Get enrollment status
        $status = (object) EnrollmentController::enrollmentStatus($activity, $user);

        // Get user enrollments, if any
        $userEnrollments = [];
        if ($user) {
            // Get enrollment for this user
            $userEnrollments = $activity->enrollments()->where([
                'user_id' => $request->user()->id
            ])->withTrashed()->with('payments')->get();
        }

        // Show view
        return view('activities.show', [
            'activity' => $activity,
            'enrollments' => $userEnrollments,
            'status' => $status
        ]);
    }

    /**
     * Handle "please login to enroll" buttons
     *
     * @param Request $request
     * @param Activity $activity
     * @return RedirectResponse
     */
    public function login(Request $request, Activity $activity): RedirectResponse
    {
        // Redirect if user is already logged in, or returning here
        if ($request->user()) {
            return redirect()
                ->route('activity.show', ['activity' => $activity]);
        }

        // Redirect to login with a backlink here
        return redirect()->guest(route('login'));
    }
}
