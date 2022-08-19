<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Helpers\Str;
use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\States\Enrollment as States;
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
        $this->middleware('can:manage,activity')->except('index');
    }

    /**
     * Shows the scanner homepage.
     */
    public function index(Request $request): HttpResponse
    {
        $user = $request->user();
        $activities = Activity::where([
            ['cancelled_at', '=', null],
            ['end_date', '>', Date::now()->addDay()],
        ])->get()->filter(fn ($activity) => $user->can('manage', $activity));

        return Response::view('enrollments.scanner.index', [
            'activities' => $activities,
        ]);
    }

    /**
     * Shows the scanner app for a given activity.
     */
    public function show(Activity $activity, Request $request): HttpResponse| RedirectResponse
    {
        if ($activity->end_date < Date::now()->addDay()) {
            flash()->warning('Deze activiteit is afgelopen, kies een andere activiteit');

            return Response::redirectToRoute('enrollments.scanner.index');
        }

        if ($activity->is_cancelled) {
            flash()->warning('Deze activiteit is geannuleerd, kies een andere activiteit');

            return Response::redirectToRoute('enrollments.scanner.index');
        }

        $csp = $this->alterCspPolicy();
        $csp->addDirective(Directive::WORKER, 'blob:');
        $csp->addDirective(Directive::IMG, 'blob:');

        return Response::view('enrollments.scanner.show', [
            'activity' => $activity,
        ]);
    }

    public function preload(Activity $activity, Request $request): JsonResponse
    {
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
}
