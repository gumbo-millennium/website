<?php

declare(strict_types=1);

namespace App\Http\Controllers\Activities;

use App\Contracts\EnrollmentServiceContract;
use App\Helpers\Str;
use App\Http\Controllers\Activities\Traits\HasEnrollments;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;

/**
 * Handles transferring enrollments
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class TransferController extends Controller
{
    use HasEnrollments;

    public function __construct()
    {
        $this->middleware('signed')->only(['show', 'accept']);
    }

    /**
     * Shows enrollment transfer view
     * @param EnrollmentServiceContract $enrollService
     * @param Request $request
     * @param Activity $activity
     * @return Response
     */
    public function sender(Request $request, Activity $activity)
    {
        // Get user
        $user = $request->user();
        \assert($user instanceof User);

        // Get enrollment
        $enrollment = $this->findActiveEnrollmentOrFail($request, $activity);

        // Render transfer link
        $transferLink = null;
        if ($enrollment->transfer_secret) {
            $transferLink = \route('enroll.transfer-view', [
                'activity' => $activity,
                'token' => $enrollment->transfer_secret
            ]);
        }

        // Render it
        return \response()
            ->view('activities.enrollments.transfer.view', compact('activity', 'enrollment', 'transferLink'))
            ->setPrivate();
    }

    /**
     * Enables or replaces the transfer code
     * @param Request $request
     * @param Activity $activity
     * @return RedirectResponse
     */
    public function senderUpdate(Request $request, Activity $activity)
    {
        // Get user
        $user = $request->user();
        \assert($user instanceof User);

        // Get enrollment
        $enrollment = $this->findActiveEnrollmentOrFail($request, $activity);

        // Update secret
        $hadToken = $enrollment->transfer_secret !== null;
        $enrollment->transfer_secret = (string) Str::uuid();
        $enrollment->save();

        // Redirect back
        $message = 'Je inschrijving is nu overdraagbaar';
        if ($hadToken) {
            $message = 'Je inschrijving is nu voorzien van een nieuwe overdrachtscode';
        }
        \flash($message, 'success');

        // Redirect back
        return \response()
            ->redirectToRoute('enroll.transfer', compact('activity'));
    }

    /**
     * Disables the transfer code
     * @param Request $request
     * @param Activity $activity
     * @return RedirectResponse
     */
    public function senderRemove(Request $request, Activity $activity)
    {
        // Get user
        $user = $request->user();
        \assert($user instanceof User);

        // Get enrollment
        $enrollment = $this->findActiveEnrollmentOrFail($request, $activity);

        // Update secret
        $enrollment->transfer_secret = null;
        $enrollment->save();

        // Redirect back
        \flash('De transfercode is gedeactiveerd', 'success');

        // Redirect back
        return \response()
            ->redirectToRoute('enroll.transfer', compact('activity'));
    }

    /**
     * Displays the enrollment with the given transfer code
     * @param Request $request
     * @param Activity $activity
     * @return RedirectResponse
     */
    public function receiver(Request $request, Activity $activity, string $token)
    {
        // Get user
        $user = $request->user();
        \assert($user instanceof User);

        // Get redirect
        $this->ensureCanTransfer($user, $activity);

        // Find enrollment
        $enrollment = $this->getEnrollmentByToken($activity, $token);

        // Get new URL
        $nextUrl = \route('enroll.transfer-view', \compact('activity', 'token'));

        // Render view
        return \response()
            ->view('activities.enrollments.transfer.accept', [
                'enrollment' => $enrollment,
                'activity' => $activity,
                'nextUrl' => $nextUrl,
            ])
            ->setPrivate();
    }

    public function receiverTake(
        EnrollmentServiceContract $enrollService,
        Request $request,
        Activity $activity,
        string $token
    ) {
        // Get user
        $user = $request->user();
        \assert($user instanceof User);

        // Get redirect
        $this->ensureCanTransfer($user, $activity);

        // Find enrollment
        $enrollment = $this->getEnrollmentByToken($activity, $token);

        // Transfer enrollment
        $enrollService->transferEnrollment($enrollment, $user);

        // Done
        \flash("Je bent nu ingeschreven voor {$activity->name}", 'success');
        return \response()
            ->redirectToRoute('activity.show', compact('activity'))
            ->setPrivate();
    }

    /**
     * Throws a fuss when the user cannot accept another enrollment
     * @param User $user
     * @param Activity $activity
     * @return void
     * @throws HttpResponseException
     */
    private function ensureCanTransfer(User $user, Activity $activity): void
    {
        // Get user enrollment
        $userEnrollment = Enrollment::findActive($user, $activity);

        // Fail if found
        if (!$userEnrollment) {
            return;
        }

        \flash('Je bent al ingeschreven voor deze activiteit', 'info');
        $redirect = \response()
            ->redirectToRoute('activity.show', compact('activity'))
            ->setPrivate();

        // Throw redirect
        throw new HttpResponseException($redirect);
    }

    /**
     * Returns the enrollment that belongs to the tranfer code
     * @param Activity $activity
     * @param string $token
     * @return Enrollment
     * @throws NotFoundHttpException
     */
    private function getEnrollmentByToken(Activity $activity, string $token): Enrollment
    {
        // Check token
        if (!Str::isUuid($token)) {
            \abort(404, 'Deze transfercode is niet geldig');
        }

        // Find enrollment
        $enrollment = Enrollment::query()
            ->withoutTrashed()
            ->whereActivityId($activity->id)
            ->whereNotState('state', [CancelledState::class, RefundedState::class])
            ->where('transfer_secret', $token)
            ->first();

        if (!$enrollment) {
            \abort(404, 'Deze transfercode is niet geldig');
        }

        return $enrollment;
    }

    /**
     * Returns true if this activity is still able to transfer enrollments
     * @param Activity $activity
     * @return bool
     */
    public function ensureOpenForTransfer(Activity $activity): bool
    {
        return $activity->start_date > now();
    }
}
