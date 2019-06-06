<?php

namespace App\Http\Controllers\Admin;

use App\Activity;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Jobs\ActivityRefundJob;

/**
 * Admin actions for the activities
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class ActivityController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Activity  $activity
     * @return \Illuminate\Http\Response
     */
    public function show(Activity $activity)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Activity  $activity
     * @return \Illuminate\Http\Response
     */
    public function edit(Activity $activity)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Activity  $activity
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Activity $activity)
    {
        //
    }

    /**
     * Cancel the activity
     *
     * @param  \App\Activity  $activity
     * @return \Illuminate\Http\Response
     */
    public function cancel(Request $request, Activity $activity)
    {
        if (!$request->has('confirm') || $request->isMethod('PATCH')) {
            return view('activity.cancel', [
                'activity' => $activity
            ]);
        }

        // Flag activity as cancelled
        $activity->cancelled = true;
        $activity->save();

        // Issue refunds if appliccable
        if ($activity->isPaid() && $request->refund) {
            ActivityRefundJob::dispatch($activity);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Activity  $activity
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Activity $activity)
    {
        if ($request->isMethod('DELETE') && $request->has('confirm')) {
            // Delete the resource
            $activity->delete();

            // Redirect to overview
            return redirect()->with([
                'status' => sprintf('De activiteit %s is verwijderd.', $activity->name)
            ]);
        }
        //
    }
}
