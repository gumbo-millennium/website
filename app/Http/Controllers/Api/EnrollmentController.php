<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\EnrollmentResource;
use App\Http\Resources\Api\EnrollmentResourceCollection;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Response;

class EnrollmentController extends Controller
{
    public function __construct()
    {
        // Ensure JSON is supported
        $this->middleware('accept-json');

        // Require auth
        $this->middleware('auth:sanctum');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $enrollments = Enrollment::query()
            ->with('activity')
            ->forUser($user)
            ->when($request->has('activity'), function ($query) use ($request) {
                $query->whereHas('activity', fn ($query) => $query->whereSlug($request->input('activity')));
            })
            ->unless($request->has('activity'), function ($query) {
                $query->whereHas('activity', fn ($query) => $query->where('start_date', '>', Date::today()->subMonth()));
            })
            ->get();

        return Response::json(EnrollmentResourceCollection::make($enrollments));
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, int $id)
    {
        $user = $request->user();

        $enrollment = Enrollment::query()
            ->with('activity')
            ->forUser($user)
            ->findOrFail($id);

        return Response::json(EnrollmentResource::make($enrollment));
    }
}
