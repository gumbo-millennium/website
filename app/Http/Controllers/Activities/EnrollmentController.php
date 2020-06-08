<?php

declare(strict_types=1);

namespace App\Http\Controllers\Activities;

use App\Http\Controllers\Activities\Traits\HandlesStripeItems;
use App\Http\Controllers\Activities\Traits\HasEnrollments;
use App\Http\Controllers\Controller;
use App\Jobs\Stripe\CreateInvoiceJob;
use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\States\Enrollment\Cancelled;
use App\Models\States\Enrollment\Confirmed;
use App\Models\States\Enrollment\Paid;
use App\Models\States\Enrollment\Seeded;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Stripe\Exception\ApiErrorException;

/**
 * Handles creating enrollments, changes in the enrollment form and unenrollment
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class EnrollmentController extends Controller
{
    use HasEnrollments;
    use HandlesStripeItems;

    /**
     * Creates the new enrollment for the activity
     * @param Request $request
     * @param Activity $activity
     * @return Response
     *
     * Does not need refactoring. All log statements are raising the
     * error thresholds.
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function create(Request $request, Activity $activity)
    {
        $user = $request->user();
        \assert($user instanceof User);

        // Redirect to the display view if the user is already enrolled
        if (Enrollment::findActive($user, $activity)) {
            logger()->info(
                'User {user} tried to enroll on {activity}, but is already enrolled.',
                compact('user', 'activity')
            );
            return redirect()->route('enroll.edit', compact('activity'));
        }

        // Check policy
        if (!$user->can('enroll', $activity)) {
            logger()->info(
                'User {user} tried to enroll on {activity}, but is not allowed to.',
                compact('user', 'activity')
            );
            return redirect()->route('activity.show', compact('activity'));
        }

        // Create new enrollment
        $enrollment = new Enrollment();

        // Assign activity and user
        $enrollment->activity()->associate($activity);
        $enrollment->user()->associate($user);

        // Determine price with and without transfer cost
        $enrollment->price = $activity->price;
        $enrollment->total_price = $activity->total_price;
        if ($user->is_member && $activity->discounts_available !== 0 && $activity->member_discount !== null) {
            logger()->info('Applying member discount {discount}', ['discount' => $activity->member_discount]);
            $enrollment->price = $activity->discount_price;
            $enrollment->total_price = $activity->total_discount_price;
        }

        // Set to null if the price is empty
        if (!is_int($enrollment->price) || $enrollment->price <= 0) {
            logger()->info('Price empty, wiping it.');
            $enrollment->price = null;
            $enrollment->total_price = null;
        }

        // Debug
        $rawPrice = $enrollment->price;
        $price = $enrollment->total_price;
        logger()->debug(
            'Assigned enrollment price of {price} ({rawPrice}).',
            compact('user', 'activity', 'rawPrice', 'price')
        );

        // Save the enrollment
        $enrollment->save();

        // Debug
        logger()->info(
            'Enrolled user {user} on {activity}. ID is {enrollment-id}.',
            [
                'user' => $user,
                'activity' => $activity,
                'enrollment' => $enrollment,
                'enrollment-id' => $enrollment->id,
            ]
        );

        // Check if the enrollment is paid
        if ($enrollment->total_price) {
            // Dispatch a job to create a payment intent and invoice
            CreateInvoiceJob::dispatch($enrollment);
        }

        // Redirect to form page if one is present
        if ($activity->form !== null) {
            logger()->debug('Form present, redirecting user');
            return redirect()->route('enroll.show', compact('activity'));
        }

        // No form present, mutate the state
        $enrollment->data = [];
        $enrollment->state->transitionTo(Seeded::class);
        $enrollment->save();

        // Forward to payment start if required
        if ($enrollment->price > 0) {
            logger()->debug('Ticket price non-zero, redirecting user');
            return redirect()->route('enroll.show', compact('activity'));
        }

        // Mark as confirmed
        $enrollment->state->transitionTo(Confirmed::class);
        $enrollment->save();

        // Redirect back to activity
        logger()->debug('Event free and no data required, redirecting back');
        return redirect()->route('activity.show', compact('activity'));
    }

    /**
     * Shows the form to update the enrollment details
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
            flash('Je kan je niet (meer) uitschrijven voor dit evenement')->info();
            return redirect()->route('enroll.show', compact('activity'));
        }

        // Show form
        return view('activities.enrollments.cancel', compact('activity', 'enrollment'));
    }

    /**
     * Confirmed unenroll requst
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
            flash('Je kan je niet meer uitschrijven voor dit evenement')->info();
            return redirect()->route('enroll.show', compact('activity'));
        }

        // Check for an "agree" thing
        $this->validate($request, [
            'accept' => 'required|accepted'
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
                    'intent' => $intent
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
                    'invoice' => $invoice
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
                'enrollment' => $enrollment
            ]);
            // Don't push error
        }
    }
}
