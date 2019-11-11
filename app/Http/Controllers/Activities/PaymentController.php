<?php

namespace App\Http\Controllers\Activities;

use App\Http\Controllers\Activities\Traits\ConsistentRedirects;
use App\Http\Controllers\Activities\Traits\CreatePaymentIntents;
use App\Http\Controllers\Activities\Traits\CreatePaymentMethods;
use App\Http\Controllers\Activities\Traits\HandlesPaymentIntents;
use App\Http\Controllers\Activities\Traits\HandlesPaymentMethods;
use App\Http\Controllers\Activities\Traits\ProvidesBankList;
use App\Http\Controllers\Controller;
use App\Jobs\Stripe\PaymentValidationJob;
use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\States\Enrollment\Cancelled;
use App\Models\States\Enrollment\Paid;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Stripe\PaymentIntent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

/**
 * Handles asking for iDEAL bank account, forwarding
 * to stripe and returning from the transaction.
 *
 * Note that usually, the webhooks will have validated the
 * payment a long time before the user returns here.
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class PaymentController extends Controller
{
    use ConsistentRedirects;
    use HandlesPaymentIntents;
    use HandlesPaymentMethods;
    use ProvidesBankList;

    /**
     * Require verified, logged-in users
     */
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
    }

    /**
     * Show the form to choose a bank for the iDEAL payment
     *
     * @return \Illuminate\Http\Response
     */
    public function form(Request $request, Activity $activity)
    {
        // Get enrollment, or a fully supplied redirect.
        $enrollment = $this->findValidEnrollmentOrRedirect($request, $activity);
        if ($enrollment instanceof RedirectResponse) {
            return $enrollment;
        }

        // Redirect to the display view if the user is already enrolled
        if ($enrollment->state->is(Paid::class)) {
            flash('Je hebt al betaald. Je inschrijving is bevestigd.', 'info');
            return redirect()->route('enroll.edit', compact('activity'));
        }

        $banks = $this->getBankList();
        return view('payment.form', compact('enrollment', 'activity', 'banks'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function start(Request $request, Activity $activity)
    {
        // Get enrollment, or a fully supplied redirect.
        $enrollment = $this->findValidEnrollmentOrRedirect($request, $activity);
        if ($enrollment instanceof RedirectResponse) {
            return $enrollment;
        }

        // Get bank list
        $bankList = $this->getBankList();
        $bankKeys = array_keys($bankList);
        $rules = [

            'bank' => ['required', 'string', Rule::in($bankKeys)],
            'accept' => 'required|accepted'
        ];

        logger()->debug("Got rules", array_merge(compact('bankList', 'rules', 'request', 'bankKeys')));

        // Validate the request
        $valid = $this->validate($request, $rules, [], [
            'bank' => 'bank naam',
            'accept' => 'voorwaarden'
        ]);

        // Fetch or create payment intent
        $intent = $this->getPaymentIntent($enrollment);

        // Redirect to the enrollment view if the user has already paid
        if ($intent->status === PaymentIntent::STATUS_SUCCEEDED) {
            dispatch(new PaymentValidationJob($enrollment));
            return redirect()->route('enroll.show', compact('activity'));
        }

        // Fetch or create payment method
        $method = $this->getIdealPaymentMethod($intent, $valid['bank']);

        if (!$method) {
            flash('Er is iets fout gegaan, probeer het later opnieuw', 'error');
            return redirect()->route('enroll.show', compact('activity'));
        }

        // Confirm the payment intent
        $intent = $this->confirmPaymentIntent($enrollment, $intent, $method);

        if (!$intent) {
            flash('Er is iets fout gegaan, probeer het later opnieuw', 'error');
            return redirect()->route('enroll.show', compact('activity'));
        }

        // Redirect
        $redirect = $this->redirectPaymentIntent($intent);
        if ($redirect) {
            return $redirect;
        }

        // Log error
        flash('Er is iets fout gegaan, probeer het later opnieuw', 'error');
        return redirect()->route('enroll.show', compact('activity'));
    }

    /**
     * Callback from Stripe
     *
     * @param Request $request
     * @param Activity $activity
     * @return Response
     */
    public function complete(Request $request, Activity $activity)
    {
        // Get user
        $user = $request->user();

        // Get enrollment, or a fully supplied redirect.
        $enrollment = $this->findValidEnrollmentOrRedirect($request, $activity);
        if ($enrollment instanceof RedirectResponse) {
            logger()->info('Recieved redirect', ['redirect' => $enrollment]);
            return $enrollment;
        }

        // Check if the enrollment is marked completed (via webhooks)
        if ($enrollment->state->is(Paid::class)) {
            logger()->notice('iDEAL payment for {enrollment} with ID {code} successful', [
                'enrollment' => $enrollment,
                'activity' => $activity,
                'user' => $enrollment->user,
                'code' => $enrollment->payment_intent,
            ]);
            flash("Je bent succesvol ingeschreven voor {$activity->title}.", 'success');
            return redirect()->route('activity.show', compact('activity'));
        }

        // Check if the enrollment is cancelled
        if ($enrollment->state->is(Cancelled::class)) {
            logger()->notice('iDEAL payment for {enrollment} with ID {code} was cancelled!', [
                'enrollment' => $enrollment,
                'activity' => $activity,
                'user' => $enrollment->user,
                'code' => $enrollment->payment_intent,
            ]);
            return redirect()->route('activity.show', compact('activity'));
        }

        // Log the API call
        logger()->info('Starting validate of {code} via Stripe.', [
            'enrollment' => $enrollment,
            'activity' => $activity,
            'user' => $enrollment->user,
            'code' => $enrollment->payment_intent,
        ]);

        // No information yet about the status. Check the Payment Intent
        $intent = $this->getPaymentIntent($enrollment);
        logger()->notice('Received intent {intent} for {enrollment}.', compact('intent', 'enrollment'));

        // Payment is processing, stand by.
        if ($intent->status === PaymentIntent::STATUS_PROCESSING) {
            logger()->info('iDEAL transaction still pending.', compact('intent'));
            return response()
                ->view('activities.payment-wait')
                ->header('Refresh', '10');
        }

        // Payment complete
        if ($intent->status === PaymentIntent::STATUS_SUCCEEDED) {
            logger()->info('iDEAL transaction completed.', compact('intent'));

            // Mark as paid
            $enrollment->state->transitionTo(Paid::class);
            $enrollment->save();

            // Flash message and continue
            flash("Je bent succesvol ingeschreven voor {$activity->title}.", 'success');
            return redirect()->route('activity.show', compact('activity'));
        }

        // The intent was cancelled (by user or system)
        if ($intent->status === PaymentIntent::STATUS_CANCELED) {
            logger()->info('iDEAL transaction cancelled.', compact('intent'));

            // Flash cancelled message
            flash('De betaling is geannuleerd. Probeer het opnieuw', 'warning');
            return redirect()->route('enroll.show', compact('activity'));
        }

        logger()->warning(
            'Received unknown response from Stripe for iDEAL request',
            compact('intent', 'enrollment', 'user', 'activity')
        );

        // Redirect to payment form
        flash('Er is iets fout gegaan, probeer het later opnieuw', 'error');
        return redirect()->route('enroll.show', compact('activity'));
    }

    /**
     * Returns an enrollment if the enrollment exists and the form data has been filled out if it's present.
     *
     * @param Request $request
     * @param Activity $activity
     * @return RedirectResponse|Enrollment
     */
    private function findValidEnrollmentOrRedirect(Request $request, Activity $activity)
    {
        // Use ConsistentRedirects first
        $enrollment = $this->findActiveEnrollmentOrRedirect($request, $activity);
        if ($enrollment instanceof RedirectResponse) {
            return $enrollment;
        }

        // Redirect to the edit view if the user hasn't completed it yet.
        if ($activity->form !== null && empty($enrollment->data)) {
            flash('Je moet eerst even onderstaand formulier invullen, alvorens te betalen', 'info');
            return redirect()->route('enroll.edit', compact('activity'));
        }

        // Return enrollment
        return $enrollment;
    }
}
