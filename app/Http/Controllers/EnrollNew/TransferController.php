<?php

declare(strict_types=1);

namespace App\Http\Controllers\EnrollNew;

use App\Facades\Enroll;
use App\Helpers\Str;
use App\Http\Controllers\Activities\Traits\HasEnrollments;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Enrollment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Response;

/**
 * Handles transferring enrollments.
 */
class TransferController extends Controller
{
    use HasEnrollments;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('throttle:10,1')
            ->only(['showConsume', 'storeConsume']);
    }

    /**
     * Show create enrollment form.
     *
     * @return HttpResponse|RedirectResponse
     */
    public function show(Request $request, Activity $activity)
    {
        if (! $enrollment = Enroll::getEnrollment($activity)) {
            return Response::redirectToRoute('enroll.create', [$activity]);
        }

        // Render transfer link
        $transferText = $transferLink = null;
        if ($enrollment->transfer_secret) {
            $transferLink = route('enroll.transfer-view', [
                'activity' => $activity,
                'secret' => $enrollment->transfer_secret,
            ]);

            $transferText = __("Hello, I'd like to transfer my enrollment for :activity to you.", [
                'activity' => $activity->name,
            ]);
        }

        // Render it
        return Response::view('enrollments.transfer', [
            'activity' => $activity,
            'enrollment' => $enrollment,
            'transferLink' => $transferLink,
            'transferText' => $transferText,
        ]);
    }

    /**
     * Create or recreate transfer link.
     */
    public function store(Request $request, Activity $activity): RedirectResponse
    {
        if (! $enrollment = Enroll::getEnrollment($activity)) {
            return Response::redirectToRoute('enroll.create', [$activity]);
        }

        // Update secret
        $hadToken = $enrollment->transfer_secret !== null;
        $enrollment->transfer_secret = hash('md5', Str::random(32));
        $enrollment->save();

        // Redirect back
        $message = $hadToken
            ? __('A new transfer code has been generated')
            : __('A transfer code has been generated, you can now transfer your enrollment');

        flash($message)->success($message);

        // Redirect back
        return Response::redirectToRoute('enroll.transfer', [$activity]);
    }

    /**
     * Remove the transfer code.
     */
    public function destroy(Activity $activity): RedirectResponse
    {
        if (! $enrollment = Enroll::getEnrollment($activity)) {
            return Response::redirectToRoute('enroll.create', [$activity]);
        }

        // Update secret
        $enrollment->transfer_secret = null;
        $enrollment->save();

        // Redirect back
        flash()->success(__('Transfer code disabled'));

        // Redirect back
        return Response::redirectToRoute('enroll.transfer', [$activity]);
    }

    /**
     * Displays the enrollment with the given transfer code.
     *
     * @return HttpResponse|RedirectResponse
     */
    public function showConsume(Request $request, Activity $activity, string $secret)
    {
        // Find enrollment
        $token = (string) $request->input('token');
        $enrollment = $this->findEnrollment($activity, $secret);

        $activeEnrollment = Enroll::getEnrollment($activity);
        if ($activeEnrollment && $activeEnrollment->is($enrollment)) {
            flash()->info(__('This is your transfer link, you cannot transfer your enrollment to yourself.'));

            return Response::redirectToRoute('enroll.transfer', [$activity]);
        }
        if ($activeEnrollment) {
            flash()->error(__('You already have an active enrollment for :activity', [
                'activity' => $activity->name,
            ]));

            return Response::redirectToRoute('enroll.show', [$activity]);
        }

        // Find enrollment
        $token = (string) $request->input('token');
        $enrollment = $this->findEnrollment($activity, $secret);

        // Fail if non-transferrable
        if (! Enroll::canTransfer($enrollment)) {
            flash()->error(__('This enrollment cannot be transferred anymore.'));

            return Response::redirectToRoute('activity.show', [$activity]);
        }

        // Render view
        return Response::view('enrollments.transfer-accept', [
            'enrollment' => $enrollment,
            'activity' => $activity,
            'acceptUrl' => route('enroll.transfer-view', [$activity, $token]),
        ])->header('Cache-Control', 'no-store, no-cache');
    }

    public function storeConsume(Request $request, Activity $activity, string $secret)
    {
        if (Enroll::getEnrollment($activity)) {
            flash()->error(__('You already have an active enrollment for :activity', [
                'activity' => $activity->name,
            ]));

            return Response::redirectToRoute('enroll.show', [$activity]);
        }

        // Find enrollment
        $token = (string) $request->input('token');
        $enrollment = $this->findEnrollment($activity, $secret);

        // Fail if non-transferrable
        if (! Enroll::canTransfer($enrollment)) {
            flash()->error(__('This enrollment cannot be transferred anymore.'));

            return Response::redirectToRoute('activity.show', [$activity]);
        }

        // Transfer enrollment
        Enroll::transferEnrollment($enrollment, $request->user());

        // Done
        flash()->success(
            __('You have successfully transferred this enrollment for :activity to your account', [
                'activity' => $activity->name,
            ]),
        );

        return Response::redirectToRoute('enroll.show', [$activity]);
    }

    /**
     * Returns the enrollment that belongs to the tranfer code.
     *
     * @throws NotFoundHttpException
     */
    private function findEnrollment(Activity $activity, string $secret): Enrollment
    {
        $enrollment = $activity->enrollments()
            ->where('transfer_secret', $secret)
            ->first();

        abort_unless($enrollment, HttpResponse::HTTP_NOT_FOUND, __('Enrollment not found'));

        return $enrollment;
    }
}
