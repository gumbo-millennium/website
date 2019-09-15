<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Activity;
use App\Models\Enrollment;

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
     * @return \Illuminate\Http\Response
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
        return view('activities.index', compact('activities', 'enrollments'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Activity  $activity
     * @return \Illuminate\Http\Response
     */
    public function show(Activity $activity)
    {
        return response()->json($activity);
    }
}
