<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Facades\Enroll;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ActivityRegisterRequest;
use App\Http\Resources\Api\ActivityResource;
use App\Models\Activity;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Response;
use Throwable;

class ActivityController extends Controller
{
    public function __construct()
    {
        // Ensure JSON is supported
        $this->middleware('accept-json');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $wantsPast = (bool) $request->input('past', false);

        $activities = Activity::query()
            ->whereAvailable($user)
            ->wherePublished()
            ->where('end_date', $wantsPast ? '<' : '>=', Date::now())
            ->paginate()
            ->appends('past', (int) $wantsPast);

        return ActivityResource::collection($activities)->toResponse($request);
    }

    public function show(Request $request, string $slug): JsonResponse
    {
        $user = $request->user();

        $activity = Activity::query()
            ->whereAvailable($user)
            ->wherePublished()
            ->whereSlug($slug)
            ->firstOrFail();

        abort_unless($activity, HttpResponse::HTTP_NOT_FOUND);

        return ActivityResource::make($activity)->toResponse($request);
    }
}
