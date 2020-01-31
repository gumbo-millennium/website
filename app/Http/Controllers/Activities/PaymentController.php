<?php

namespace App\Http\Controllers\Activities;

use App\Contracts\StripeServiceContract;
use App\Exceptions\EnrollmentNotFoundException;
use App\Http\Controllers\Activities\Traits\HasEnrollments;
use App\Http\Controllers\Controller;
use App\Jobs\Stripe\PaymentValidationJob;
use App\Models\Activity;
use App\Models\States\Enrollment\Cancelled;
use App\Models\States\Enrollment\Paid;
use App\Services\IdealBankService;
use App\Services\StripeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use RuntimeException;
use Stripe\Exception\InvalidArgumentException;
use Stripe\Source;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

/**
 * Handles asking for iDEAL bank account, forwarding
 * to stripe and returning from the transaction.
 *
 * Note that usually, the webhooks will have validated the
 * payment a long time before the user returns here.
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PaymentController extends Controller
{
    use HasEnrollments;

    /**
     * Require verified, logged-in users
     */
    public function __construct()
    {
        $this->middleware(['auth', 'verified'])->except('resume');
        $this->middleware(['signed'])->only('resume');
    }

    /**
     * Show the form to choose a bank for the iDEAL payment
     *
     * @param StripeServiceContract $stripeService
     * @param IdealBankService $bankService
     * @param Request $request
     * @param Activity $activity
     * @return Illuminate\Http\RedirectResponse|Illuminate\Http\Response
     * @throws RouteNotFoundException
     */
    public function show(
        StripeServiceContract $stripeService,
        IdealBankService $bankService,
        Request $request,
        Activity $activity
    ) {
        // Get enrollment
        $enrollment = $this->findActiveEnrollmentOrFail($request, $activity);

        // Redirect to the display view if the user is already enrolled
        if ($enrollment->state->is(Paid::class)) {
            flash('Je hebt al betaald. Je inschrijving is bevestigd.', 'info');
            return redirect()->route('activity.show', compact('activity'));
        }

        // Invoice lines and bank list
        $quotedInvoice = $stripeService->getComputedInvoiceLines($enrollment);
        $invoiceLines = $quotedInvoice->get('items', []);
        $invoiceCoupon = $quotedInvoice->get('coupon');
        $banks = $bankService->getAll();
        return response()
            ->view(
                'activities.enrollments.payment',
                compact('enrollment', 'activity', 'banks', 'invoiceLines', 'invoiceCoupon')
            )
            ->setPrivate();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(
        IdealBankService $bankService,
        StripeServiceContract $stripeService,
        Request $request,
        Activity $activity
    ) {
        // Get enrollment
        $enrollment = $this->findActiveEnrollmentOrFail($request, $activity);

        // Get bank list
        $bankList = $bankService->codes();
        $rules = [
            'bank' => ['required', 'string', Rule::in($bankList)],
            'accept' => 'required|accepted'
        ];

        logger()->debug("Got rules", array_merge(compact('bankList', 'rules', 'request')));

        // Validate the request
        $valid = $this->validate($request, $rules, [], [
            'bank' => 'bank naam',
            'accept' => 'voorwaarden'
        ]);

        // Get invoice (for sanity)
        $invoice = $stripeService->getInvoice($enrollment);
        logger()->info('Going to pay {invoice}.', compact('invoice'));

        // Build source
        $source = $stripeService->getSource($enrollment, $valid['bank']);
        logger()->info('Built source {source}.', compact('source'));

        // Redirect to 'please wait' page
        return response()
            ->view('activities.enrollments.payment-go')
            ->header('Refresh', '1;url=' . route('enroll.pay-wait', compact('activity')))
            ->setPrivate();
    }

    /**
     * Try to start iDEAL
     * @param Request $request
     * @param StripeService $stripeService
     * @param Activity $activity
     * @return RedirectResponse|Response
     * @throws EnrollmentNotFoundException
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function start(Request $request, StripeService $stripeService, Activity $activity)
    {
        // Get enrollment
        $enrollment = $this->findActiveEnrollmentOrFail($request, $activity);

        // Build source
        $source = $stripeService->getSource($enrollment, null);
        logger()->info('Re-retrieved source {source}.', compact('source'));

        // Check for redirect
        $redirect = $stripeService->getSourceRedirect($source);
        if ($redirect) {
            logger()->info('And away we go! Redirecting to Stripe.');
            return $redirect;
        }

        // Redirect to 'please wait' page
        return response()
            ->view('activities.enrollments.payment-go')
            ->header('Refresh', '1;url=' . route('pay-wait', compact('activity')))
            ->setPrivate();
    }

    /**
     * Callback from Stripe
     *
     * @param Request $request
     * @param Activity $activity
     * @return Response
     */
    public function complete(Request $request, StripeService $stripeService, Activity $activity)
    {
        // Get enrollment
        $enrollment = $this->findActiveEnrollmentOrFail($request, $activity);

        // Check if the Webhook had already caught it
        if ($enrollment->state->isOneOf(Paid::class, Cancelled::class)) {
            return response()
                ->redirectToRoute('activity.show', compact('activity'))
                ->setPrivate();
        }

        // Check it ourselves
        PaymentValidationJob::dispatchNow($enrollment);

        // Reload model
        $enrollment->refresh();

        // Check if the Webhook had already caught it
        if ($enrollment->state instanceof Paid) {
            logger()->notice('iDEAL payment for {enrollment} with ID {code} successful', [
                'enrollment' => $enrollment,
                'activity' => $activity,
                'user' => $enrollment->user,
                'code' => $enrollment->payment_invoice,
            ]);
            flash("Je bent succesvol ingeschreven voor {$activity->name}.", 'success');
            return response()
                ->redirectToRoute('activity.show', compact('activity'))
                ->setPrivate();
        }

        // Get source
        $source = $stripeService->getSource($enrollment, null);
        if (in_array($source->status, [Source::STATUS_CANCELED, Source::STATUS_FAILED])) {
            flash("De betaling voor {$activity->name} is mislukt of geannuleerd.", 'info');
            return response()
                ->redirectToRoute('activity.show', compact('activity'))
                ->setPrivate();
        }

        // Queue a re-check
        PaymentValidationJob::dispatch($enrollment);

        // Redirect
        flash("We kijken je betaling even na, hold tight..", 'info');
        return response()
            ->redirectToRoute('activity.show', compact('activity'))
            ->setPrivate();
    }
}
