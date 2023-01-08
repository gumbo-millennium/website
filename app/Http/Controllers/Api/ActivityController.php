<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ActivityResource;
use App\Http\Resources\Api\ActivityResourceCollection;
use App\Models\Activity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Response;
use OpenApi\Attributes as OA;

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
        if ($request->has('enrolled') && $user == null) {
            return Response::json([
                'ok' => false,
                'error' => [
                    'code' => 400_401,
                    'message' => 'You must be logged in to view your enrolled activities.',
                ],
            ], 400);
        }

        $isPersonalised = $user != null;

        $activities = Activity::query()
            ->whereAvailable($user)
            ->wherePublished()
            ->when($request->has('enrolled'), function ($query) use ($user) {
                $query->withEnrollmentsFor($user);
                $query->whereHas('enrollments', fn ($query) => $query->where('user_id', $user->id));
            })
            ->paginate();

        return Response::json(ActivityResourceCollection::make($activities));
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    #[OA\Get(
        path: '/api/activities',
        responses: [
            new OA\Response(response: 200, description: 'AOK'),
            new OA\Response(response: 401, description: 'Not allowed'),
        ],
    )]
    public function show(Request $request, string $slug): JsonResponse
    {
        $user = $request->user();

        $activity = Activity::query()
            ->whereAvailable($user)
            ->wherePublished()
            ->whereSlug($slug)
            ->firstOrFail();

        abort_unless($activity, HttpResponse::HTTP_NOT_FOUND);

        return Response::json(ActivityResource::make($activity));
    }
}
