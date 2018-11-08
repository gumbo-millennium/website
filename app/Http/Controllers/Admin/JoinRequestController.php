<?php

namespace App\Http\Controllers;

use App\JoinRequest;
use Illuminate\Http\Request;
use App\Http\Requests\JoinChangeRequest;

class JoinRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Get queries
        $pendingQuery = JoinRequest::pending();
        $completedQuery = JoinRequest::accepted();
        $rejectedQuery = JoinRequest::declined();

        // Get counts
        $counts = [
            'all' => JoinRequest::count(),
            'pending' => $pendingQuery->count(),
            'completed' => $completedQuery->count(),
            'rejected' => $rejectedQuery->count(),
        ];

        // Get actual vehicles
        $pending = $pendingQuery->paginate(20);
        $completed = $completedQuery->paginate(20);
        $rejected = $rejectedQuery->paginate(20);

        return view('admin.requests.list', [
            'pending' => $pending,
            'completed' => $completed,
            'rejected' => $rejected,
            'count' => $count
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\JoinRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function show(JoinRequest $request)
    {
        return view('admin.requests.details', [
            'request' => $request
        ])
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\JoinRequest  $joinRequest
     * @return \Illuminate\Http\Response
     */
    public function edit(JoinChangeRequest $request, JoinRequest $joinRequest)
    {
        $status = $request->status;

        // Go back to the previous page
        if ($status === $joinRequest->status) {
            return redirect()->route('admin.join.manage', [
                'request' => $joinRequest
            ]);
        }

        // Get and update request
        $joinRequest->status = $status;
        $joinRequest->save();

        // Redirect back to the object
        return redirect()->route('admin.join.manage', [
            'request' => $joinRequest
        ])->with([
            'status' => trans('join.messages.updated', [
                'state' => trans("join.status.{$joinRequest->state}"),
                'request' => $joinRequest->id
            ])
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\JoinRequest  $joinRequest
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, JoinRequest $joinRequest)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\JoinRequest  $joinRequest
     * @return \Illuminate\Http\Response
     */
    public function destroy(JoinRequest $joinRequest)
    {
        //
    }
}
