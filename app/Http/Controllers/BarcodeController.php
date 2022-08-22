<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Helpers\Str;
use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\States\Enrollment as States;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Response;
use Spatie\Csp\Directive;

class BarcodeController extends Controller
{
    /**
     * Converst the Enrollment's ticket_code to a salted hash, returning only the first 12 characters.
     */
    public static function barcodeToSecretHash(string $salt, Enrollment $enrollment): string
    {
        return substr(hash('sha256', Str::upper("{$salt}{$enrollment->ticket_code}")), 0, 12);
    }

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:manage,App\Models\Activity')->only('index');
        $this->middleware('can:manage,activity')->except('index');
    }

    /**
     * Shows the scanner homepage.
     */
    public function index(Request $request): HttpResponse
    {
        $user = $request->user();

        $activities = $this->getActivityQuery()->get()
            ->filter(fn ($activity) => $user->can('manage', $activity));

        return Response::view('enrollments.scanner.index', [
            'activities' => $activities,
        ]);
    }

    /**
     * Shows the scanner app for a given activity.
     */
    public function show(Activity $activity, Request $request): HttpResponse| RedirectResponse
    {
        // Check if the activity is eligible for scanning
        if (! $this->activityIsEligible($activity)) {
            flash()->info('Deze activiteit is niet beschikbaar voor scannen.');

            return Response::redirectToRoute('barcode.index');
        }

        $csp = $this->alterCspPolicy();
        $csp->addDirective(Directive::WORKER, 'blob:');

        return Response::view('enrollments.scanner.show', [
            'activity' => $activity,
        ]);
    }

    /**
     * Returns a list of partial hashes for barcodes that match this
     * activity.
     */
    public function preload(Activity $activity): JsonResponse
    {
        if (! $this->activityIsEligible($activity)) {
            Response::json([
                'ok' => false,
                'message' => 'Deze activiteit is niet beschikbaar voor scannen.',
            ], HttpResponse::HTTP_BAD_REQUEST);
        }

        $enrollments = $activity
            ->enrollments()
            ->whereState('state', [States\Confirmed::class, States\Paid::class])
            ->get([
                'id',
                'ticket_code',
            ]);

        $salt = Str::upper(Str::random(16));

        return Response::json([
            'ok' => true,
            'data' => [
                'salt' => $salt,
                'barcodes' => $enrollments->map(fn (Enrollment $enrollment) => self::barcodeToSecretHash($salt, $enrollment)),
            ],
        ]);
    }

    /**
     * Consumes the given barcode, can only be called once.
     */
    public function consume(Activity $activity, Request $request): JsonResponse
    {
        if (! $this->activityIsEligible($activity)) {
            Response::json([
                'ok' => false,
                'code' => 400,
                'message' => 'Deze activiteit is niet beschikbaar voor scannen.',
            ], HttpResponse::HTTP_BAD_REQUEST);
        }

        $request->validate([
            'barcode' => 'required|string',
        ]);

        $barcode = $request->input('barcode');
        $enrollment = $activity->enrollments()
            ->where('ticket_code', $barcode)
            ->whereState('state', [States\Confirmed::class, States\Paid::class])
            ->first();

        if (! $enrollment) {
            return Response::json([
                'ok' => false,
                'code' => 404,
                'message' => 'Barcode not found',
            ], HttpResponse::HTTP_NOT_FOUND);
        }

        if ($enrollment->consumed_at) {
            return Response::json([
                'ok' => false,
                'code' => 409,
                'message' => 'Barcode already consumed',
            ], HttpResponse::HTTP_CONFLICT);
        }

        // Assign consumed at and consumed by
        $enrollment->consumed_at = Date::now();
        $enrollment->consumedBy()->associate($request->user());

        // Clear transfer secret, no use in transferring a consumed ticket
        $enrollment->transfer_secret = null;

        // Save changes
        $enrollment->save();

        return Response::json([
            'ok' => true,
            'code' => 200,
            'message' => 'Barcode consumed',
        ]);
    }

    /**
     * Returns a query that matches activities that the user can scan.
     *
     * @return Activity|Builder
     */
    private function getActivityQuery(): Builder
    {
        return Activity::query()
            ->has('tickets')
            // Hasn't ended, or ended less than 6 hours ago
            ->where('end_date', '>=', Date::now()->subHours(6))
            // Not cancelled
            ->whereNull('cancelled_at')
            // Order by start date, and end date
            ->orderBy('start_date', 'asc')
            ->orderBy('end_date', 'asc');
    }

    /**
     * Returns if this activity is eligible for scanning.
     */
    private function activityIsEligible(Activity $activity): bool
    {
        return $this->getActivityQuery()->where('id', $activity->id)->exists();
    }
}
