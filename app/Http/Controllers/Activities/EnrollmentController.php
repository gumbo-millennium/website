<?php

declare(strict_types=1);

namespace App\Http\Controllers\Activities;

use App\Contracts\EnrollmentServiceContract;
use App\Http\Controllers\Activities\Traits\HandlesStripeItems;
use App\Http\Controllers\Activities\Traits\HasEnrollments;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\States\Enrollment\Cancelled;
use App\Models\States\Enrollment\Confirmed;
use App\Models\States\Enrollment\Paid;
use App\Models\User;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\ApiErrorException;

/**
 * Handles creating enrollments, changes in the enrollment form and unenrollment
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class EnrollmentController extends Controller
{
    use HasEnrollments;
    use HandlesStripeItems;

    /**
     * Creates the new enrollment for the activity
     *
     * @param EnrollmentServiceContract $enrollService
     * @param Request $request
     * @param Activity $activity
     * @return Response
     */
    public function create(
        EnrollmentServiceContract $enrollService,
        Request $request,
        Activity $activity
    ) {
        // Get user
        $user = $request->user();
        \assert($user instanceof User);

        // Check if we need to lock
        $lock = $enrollService->useLocks() ? $enrollService->getLock($activity) : null;

        // Get enrollment
        $enrollment = null;

        try {
            // Get a lock
            optional($lock)->block(15);

            // Check if the user can actually enroll
            if (!$enrollService->canEnroll($activity, $user)) {
                Log::info('User {user} tried to enroll into {actiity}, but it\'s not allowed', [
                    'user' => $user,
                    'activity' => $activity,
                ]);

                // Redirect properly
                $isEnrolled = Enrollment::findActive($user, $activity) !== null;
                return \redirect()
                    ->route($isEnrolled ? 'enroll.edit' : 'activity.show', compact('activity'));
            }

            // Create the enrollment
            $enrollment = $enrollService->createEnrollment($activity, $user);
        } catch (LockTimeoutException $exception) {
            // Report timeout
            \report($exception);

            // Write message
            \flash('Sorry, het is erg druk momenteel, probeer het zometeen nogmaals', 'warning');

            // Redirect back
            return \redirect()->route('activity.show', compact('activity'));
        } finally {
            // Free lock
            \optional($lock)->release();
        }

        // Advance the enrollment
        $enrollService->advanceEnrollment($activity, $enrollment);

        // Check if completed
        if ($enrollment->state instanceof Confirmed) {
            // Flash ok
            \flash("Je bent ingeschreven voor {$activity->name}", 'success');

            // Redirect to activity
            return redirect()->route('activity.show', compact('activity'));
        }

        // Redirect to tunnel
        return redirect()->route('enroll.show', compact('activity'));
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
        // Get enrollment
        $enrollment = $this->findActiveEnrollmentOrFail($request, $activity);

        // Instantly redirect to payment controller if no form is present
        if ($activity->form === null) {
            return $this->redirect($enrollment);
        }

        // Ask for a redirect if the event is in the past.
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
        // Get enrollment
        $enrollment = $this->findActiveEnrollmentOrFail($request, $activity);

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
        if ($enrollment->price > 0 && $enrollment->state->isOneOf(Paid::class, Cancelled::class)) {
            if ($enrollment->activity->end_date < now()) {
                flash(
                    'Het evenement is afgelopen, maar je moet nog betalen. Gelieve dat meteen even te doen.',
                    'warning'
                );
            }
            return redirect()->route('payment.start', compact('activity'));
        }

        // In all other cases (including past-events), redirect the user to the enrollment details
        return redirect()->route('enroll.show', compact('activity'));
    }

    /**
     * Unenroll form
     *
     * @param Request $request
     * @param Activity $activity
     * @return Response
     */
    public function delete(Request $request, Activity $activity)
    {
        // Get enrollment
        $enrollment = $this->findActiveEnrollmentOrFail($request, $activity);

        // Ask policy
        if (!$request->user()->can('unenroll', $enrollment)) {
            flash('Je kan je niet (meer) uitschrijven voor dit evenement', 'info');
            return redirect()->route('enroll.show', compact('activity'));
        }

        // Show form
        return view('activities.enrollments.cancel', compact('activity', 'enrollment'));
    }

    /**
     * Confirmed unenroll requst
     *
     * @param Request $request
     * @param Activity $activity
     * @return Response
     */
    public function destroy(Request $request, Activity $activity)
    {
        // Get enrollment and user
        $enrollment = $this->findActiveEnrollmentOrFail($request, $activity);
        $user = $request->user();

        // Ask policy
        if (!$user->can('unenroll', $enrollment)) {
            flash('Je kan je niet meer uitschrijven voor dit evenement', 'info');
            return redirect()->route('enroll.show', compact('activity'));
        }

        // Check for an "agree" thing
        $this->validate($request, [
            'accept' => 'required|accepted',
        ]);

        // Log
        logger()->info('Unenrolling {user} from {activity}', compact('user', 'activity', 'enrollment'));

        // Transition to cancelled
        $enrollment->state->transitionTo(Cancelled::class);
        $enrollment->save();

        // Done :)
        flash("Je bent uitgeschreven voor {$activity->name}.", 'sucess');
        return redirect()->route('activity.show', compact('activity'));
    }

    /**
     * Adds a Stripe Payment Intent or Stripe Invoice to the enrollment, if required.
     *
     * @param Enrollment $enrollment
     * @return void
     */
    protected function addPaymentObject(Enrollment $enrollment): void
    {
        // Free enrollments don't need a payment object.
        if (!$enrollment->price) {
            return;
        }

        // Get activity
        $activity = $enrollment->activity;

        try {
            // First case: Check for intent-based payments (iDeal on-site)
            if ($activity->payment_type === Activity::PAYMENT_TYPE_INTENT) {
                // Create intent
                $intent = $this->getPaymentIntent($enrollment);

                // Log result
                logger()->info('Created Stripe payment intent {intent}.', [
                    'intent' => $intent,
                ]);

                // Assign
                $enrollment->payment_intent = $intent->id;
            }

            // Second case: Check for invoice-based payments (iDeal via e-mail)
            if ($activity->payment_type === Activity::PAYMENT_TYPE_BILLING) {
                // Create invoice
                $invoice = $this->getPaymentInvoice($enrollment);

                // Log result
                logger()->info('Created Stripe invoice {invoice}.', [
                    'invoice' => $invoice,
                ]);

                // Assign
                $enrollment->payment_invoice = $invoice->id;
            }

            // Save changes
            $enrollment->save();
        } catch (ApiErrorException $e) {
            // Log error
            logger()->error("Recieved API error whilst creating intent for {enrollment}", [
                'exception' => $e,
                'enrollment' => $enrollment,
            ]);
            // Don't push error
        }
    }
}
