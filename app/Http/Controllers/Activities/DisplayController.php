<?php

namespace App\Http\Controllers\Activities;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Enrollment;
use App\ViewModels\ActivityViewModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Handles showing activity lists, activities and the schedule route
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class DisplayController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $user = $request->user();
        // Get all future events by default
        $query = Activity::query()
            ->where('end_date', '>', now())
            ->orderBy('start_date');

        // Get all past events instead
        if ($request->has('past')) {
            $query = Activity::query()
                ->where('end_date', '<=', now())
                ->orderByDesc('end_date');
        }

        // Only show activities availabe to this user.
        $query = $query->available();

        // Paginate the response
        $activities = $query->paginate();

        // Collect an empty list of enrollments
        $enrollments = collect();

        if ($user) {
            // Get all user enrollments, indexed by the activity_id
            $enrollments = Enrollment::query()
                ->whereUserId($request->user()->id)
                ->whereIn('activity_id', $activities->pluck('id'))
                ->orderBy('created_at', 'asc')
                ->get()
                ->keyBy('activity_id');
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
        // Ensure the user can see this
        $this->authorize('view', $activity);

        // Load enrollments
        $activity->load(['enrollments']);

        // Show view
        return view('activities.show', new ActivityViewModel(
            $request->user(),
            $activity
        ));
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
