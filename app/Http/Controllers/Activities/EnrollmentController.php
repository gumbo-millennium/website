<?php

declare(strict_types=1);

namespace App\Http\Controllers\Activities;

use App\Http\Controllers\Activities\Traits\ConsistentRedirects;
use App\Http\Controllers\Activities\Traits\HandlesStripeItems;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\States\Enrollment\Cancelled;
use App\Models\States\Enrollment\Confirmed;
use App\Models\States\Enrollment\Paid;
use App\Models\States\Enrollment\Seeded;
use App\Models\User;
use App\Services\StripeErrorService;
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
    use HandlesStripeItems;

    /**
     * Require verified, logged-in users
     */
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
    }

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
        return view('enrollment.index', [
            'enrollments' => $query->paginate(20)
        ]);
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
        return view('enrollments.show', [
            'activity' => $activity,
            'enrollment' => $enrollment
        ]);
    }

    /**
     * Creates the new enrollment for the activity
     *
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
    public function store(Request $request, Activity $activity)
    {
        /** @var User $user */
        $user = $request->user();

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
        $enrollment->price = $activity->price_guest;
        $enrollment->total_price = $activity->total_price_guest;
        if ($user->is_member) {
            $enrollment->price = $activity->price_member;
            $enrollment->total_price = $activity->total_price_member;
        }

        // Set to null if the price is empty
        if (!is_int($enrollment->price) || $enrollment->price <= 0) {
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

        // Adds a payment object (intent or draft invoice) to the enrollment
        $this->addPaymentObject($enrollment);

        // Redirect to form page if one is present
        if ($activity->form !== null) {
            logger()->debug('Form present, redirecting to edit');
            return redirect()->route('enroll.edit', compact('activity'));
        }

        // No form present, mutate the state
        $enrollment->data = [];
        $enrollment->state->transitionTo(Seeded::class);
        $enrollment->save();

        // Forward to payment
        if ($enrollment->price > 0) {
            logger()->debug('Price present, redirecting to payment');

            if ($activity->payment_type === Activity::PAYMENT_TYPE_INTENT) {
                flash(
                    'Je plek is gereserveerd. Rond de betaling binnen 72 uur af om de inschrijving te bevestigen.',
                    'info'
                );
                return redirect()->route('payment.start', compact('activity'));
            }

            flash(
                'Je plek is gereserveerd. Bevestig je inschrijving binnen 72 uur om je plek te behouden.',
                'info'
            );
            return redirect()->route('payment.start', compact('activity'));
        }

        // Mark as confirmed
        $enrollment->state->transitionTo(Confirmed::class);
        $enrollment->save();

        // Redirect back to activity
        return $this->redirect($enrollment);
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
        // Get enrollment, or a fully supplied redirect.
        $enrollment = $this->findActiveEnrollmentOrRedirect($request, $activity);
        if ($enrollment instanceof RedirectResponse) {
            return $enrollment;
        }

        // Ask policy
        if (!$request->user()->can('unenroll', $enrollment)) {
            flash('Je kan je niet meer uitschrijven voor dit evenement', 'info');
            return redirect()->route('enroll.show', compact('activity'));
        }

        // Show form
        return view('enrollments.cancel', compact('activity', 'enrollment'));
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
        $user = $request->user();
        // Get enrollment, or a fully supplied redirect.
        $enrollment = $this->findActiveEnrollmentOrRedirect($request, $activity);
        if ($enrollment instanceof RedirectResponse) {
            return $enrollment;
        }

        // Ask policy
        if (!$user->can('unenroll', $enrollment)) {
            flash('Je kan je niet meer uitschrijven voor dit evenement', 'info');
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
        flash("Je bent uitgeschreven voor {$activity->title}.", 'sucess');
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
