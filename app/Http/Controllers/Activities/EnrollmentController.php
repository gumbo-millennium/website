<?php

declare(strict_types=1);

namespace App\Http\Controllers\Activities;

use App\Http\Controllers\Activities\Traits\ConsistentRedirects;
use App\Http\Controllers\Activities\Traits\CreatePaymentIntents;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\States\Enrollment\Confirmed;
use App\Models\States\Enrollment\Paid;
use App\Models\User;
use App\Service\StripeErrorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Handles creating enrollments, changes in the enrollment form and unenrollment
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class EnrollmentController extends Controller
{
    use ConsistentRedirects;

    /**
     * Lists all enrollments
     *
     * @param Request $request
     * @param Activity $activity
     * @return Response
     */
    public function index(Request $request)
    {
        // Get user
        $user = $request->user();

        // Do we want to show old enrollments?
        $compare = $request->has('old') ? '<' : '>=';

        // Build query
        $query = Enrollment::whereUserId($user->id)
            ->where('activity.end_date', $compare, now())
            ->with(['activity']);

        // Return view
        return view('enrollment.show', [
            'enrollments' => $query->paginate(20)
        ]);
    }

    /**
     * Creates the new enrollment for the activity
     *
     * @param Request $request
     * @param Activity $activity
     * @return Response
     */
    public function store(Request $request, Activity $activity)
    {
        /** @var User $user */
        $user = $request->user();

        // Redirect to the display view if the user is already enrolled
        if (Enrollment::findActive($user, $activity)) {
            return redirect()->route('enroll.edit', compact('activity'));
        }

        if (!$user->can('enroll', $activity)) {
            return redirect()->route('activity.show', compact('activity'));
        }

        // Create new enrollment
        $enrollment = new Enrollment();

        // Assign activity and user
        $enrollment->activity()->associate($activity);
        $enrollment->user()->associate($user);

        // Determine price, converting a "0" value to null.
        $enrollment->price = $user->is_member ? $activity->price_member : $activity->price_guest;
        if (!is_int($enrollment->price) || $enrollment->price <= 0) {
            $enrollment->price = null;
        }

        // Redirect to form page
        if ($activity->form !== null) {
            return redirect()->route('enroll.edit', compact('activity'));
        }

        // Redirect to next page
        $this->redirectCreate($enrollment);
    }

    /**
     * Shows the enrollment for the given activity
     *
     * @param Request $request
     * @param Activity $activity
     * @return Response
     */
    public function show(Request $request, Activity $activity)
    {
        // Get enrollment, or a fully supplied redirect.
        $enrollment = $this->findActiveEnrollmentOrRedirect($request, $activity);
        if ($enrollment instanceof RedirectResponse) {
            return $enrollment;
        }

        // Return JSON response
        return response()->json([
            'activity' => $activity,
            'enrollment' => $enrollment
        ]);
    }

    /**
     * Shows the form to update the enrollment details
     *
     * @param Request $request
     * @param Activity $activity
     * @return Response
     */
    public function edit(Request $request, Activity $activity)
    {
        // Get enrollment, or a fully supplied redirect.
        $enrollment = $this->findActiveEnrollmentOrRedirect($request, $activity);
        if ($enrollment instanceof RedirectResponse) {
            return $enrollment;
        }

        // Instantly redirect to payment controller if no form is present
        if ($activity->form === null) {
            return $this->redirect($enrollment);
        }

        // Redirect to payment provider or details if the event is in the past.
        if ($activity->end_date < now()) {
            return $this->redirect($enrollment);
        }

        // Render form. TODO
        return response('HERE IS A FORM');
    }

    /**
     * Creates the new enrollment for the activity
     *
     * @param Request $request
     * @param Activity $activity
     * @return Response
     */
    public function update(Request $request, Activity $activity)
    {
        // Get enrollment, or throw a 404
        $enrollment = Enrollment::findActiveOrFail($request->user(), $activity);

        // Instantly redirect to next page if no form is available
        if ($activity->form === null) {
            return $this->redirect($enrollment);
        }

        // Build form validation array
        // TODO

        // Validate form
        $validated = $request->validate();

        // Store data from form in enrollment
        $enrollment->data = $validated;
        $enrollment->save();

        // Redirect to next route
        return $this->redirect($enrollment);
    }

    /**
     * Redirects users to the proper route. Note that this route might redirect
     * to the payments, even if the activity has ended.
     *
     * @param Enrollment $enrollment
     * @return RedirectResponse
     */
    public function redirect(Enrollment $enrollment): RedirectResponse
    {
        $activity = $enrollment->activity;

        // Redirect to activity if the activity has ended or if the enrollment is trashed
        if ($enrollment->trashed()) {
            return redirect()->route('activity.show', compact('activity'));
        }

        // Redirect to the payment provider if the enrollment is not confirmed yet and
        // has a price > 0. This allows users to pay after the activity has taken place.
        if (!($enrollment->state instanceof Confirmed) && $enrollment->price) {
            return redirect()->route('payments.start', compact('activity'));
        }

        // In all other cases (including past-events), redirect the user to the enrollment details
        return redirect()->route('enroll.show', compact('activity'));
    }
}
