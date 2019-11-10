<?php

namespace App\Http\Controllers\Activities;

use App\Http\Controllers\Activities\Traits\ConsistentRedirects;
use App\Http\Controllers\Activities\Traits\CreatePaymentIntents;
use App\Http\Controllers\Activities\Traits\CreatePaymentMethods;
use App\Http\Controllers\Activities\Traits\HandlesPaymentIntents;
use App\Http\Controllers\Activities\Traits\HandlesPaymentMethods;
use App\Http\Controllers\Activities\Traits\ProvidesBankList;
use App\Http\Controllers\Controller;
use App\Jobs\PaymentValidationJob;
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
        if ($activity->status->is(Paid::class)) {
            flash('Je hebt al betaald. Je inschrijving is bevestigd.', 'info');
            return redirect()->route('enroll.edit', compact('activity'));
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request, Activity $activity)
    {
        // Get enrollment, or a fully supplied redirect.
        $enrollment = $this->findValidEnrollmentOrRedirect($request, $activity);
        if ($enrollment instanceof RedirectResponse) {
            return $enrollment;
        }

        // Get bank list
        $bankList = $this->getBankList();

        // Validate the request
        $valid = Validator::make($request->all(), [
            'bank' => ['required', 'string', Rule::in($bankList)],
            'terms' => 'required|accepted'
        ])->validate();

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
        // Get enrollment, or a fully supplied redirect.
        $enrollment = $this->findValidEnrollmentOrRedirect($request, $activity);
        if ($enrollment instanceof RedirectResponse) {
            return $enrollment;
        }

        // Check if the enrollment is marked completed (via webhooks)
        if ($enrollment->state->is(Paid::class)) {
            flash("Je bent succesvol ingeschreven voor {$activity->title}.", 'success');
            return redirect()->route('activity.show', compact('activity'));
        }

        // Check if the enrollment is cancelled
        if ($enrollment->state->is(Cancelled::class)) {
            return redirect()->route('activity.show', compact('activity'));
        }

        // No information yet about the status. Check the Payment Intent
        $intent = $this->getPaymentIntent($enrollment);

        // Payment is processing, stand by.
        if ($intent->status === PaymentIntent::STATUS_PROCESSING) {
            return response()
                ->view('activities.payment-wait')
                ->header('Refresh', '10');
        }

        // Payment complete
        if ($intent->status === PaymentIntent::STATUS_SUCCEEDED) {
            $enrollment->state->transitionTo(Paid::class);
            flash("Je bent succesvol ingeschreven voor {$activity->title}.", 'success');
            return redirect()->route('activity.show', compact('activity'));
        }

        // The intent was cancelled (by user or system)
        if ($intent->status === PaymentIntent::STATUS_CANCELED) {
            flash('De betaling is geannuleerd. Probeer het opnieuw', 'warning');
            return redirect()->route('enroll.show', compact('activity'));
        }

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
