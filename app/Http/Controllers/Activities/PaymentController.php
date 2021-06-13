<?php

declare(strict_types=1);

namespace App\Http\Controllers\Activities;

use App\Contracts\StripeServiceContract;
use App\Forms\PaymentStartForm;
use App\Helpers\Arr;
use App\Http\Controllers\Activities\Traits\HasEnrollments;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\States\Enrollment\Cancelled;
use App\Models\States\Enrollment\Paid;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Kris\LaravelFormBuilder\FormBuilder;
use Stripe\Source;

/**
 * Handles asking for iDEAL bank account, forwarding
 * to stripe and returning from the transaction.
 *
 * Note that usually, the webhooks will have validated the
 * payment a long time before the user returns here.
 */
class PaymentController extends Controller
{
    use HasEnrollments;

    /**
     * Show the form to choose a bank for the iDEAL payment.
     *
     * @return Illuminate\Http\RedirectResponse|Illuminate\Http\Response
     * @throws RouteNotFoundException
     */
    public function show(
        StripeServiceContract $stripeService,
        FormBuilder $formBuilder,
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

        // Invoice lines and discount
        $invoiceLines = $stripeService->getComputedInvoiceLines($enrollment);
        $invoiceCoupon = $enrollment->is_discounted ? $stripeService->getComputedCoupon($enrollment->activity) : null;

        // Form
        $form = $formBuilder->create(PaymentStartForm::class, [
            'method' => 'POST',
            'url' => route('enroll.pay', compact('activity')),
        ]);

        // Build response
        return response()
            ->view(
                'activities.enrollments.payment',
                compact('enrollment', 'activity', 'form', 'invoiceLines', 'invoiceCoupon')
            )
            ->setPrivate();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(FormBuilder $formBuilder, Request $request, Activity $activity)
    {
        // Get enrollment
        $enrollment = $this->findActiveEnrollmentOrFail($request, $activity);

        // Ensure the user actually needs to pay for this
        if (! $enrollment->price) {
            logger()->warning('Tried to "pay" for an enrollment that\'s free.', compact('activity', 'enrollment'));

            return response()
                ->redirectToRoute('enroll.show', compact('activity'))
                ->setPrivate();
        }

        // Get form
        $form = $formBuilder->create(PaymentStartForm::class);

        // Or automatically redirect on error. This will throw an HttpResponseException with redirect
        $form->redirectIfNotValid();

        // Get bank
        $bankName = Arr::get($form->getFieldValues(), 'bank');

        // Store bank and expire the thing in an hour
        $request->session()->put("enroll.{$enrollment->id}.bank", $bankName);
        $request->session()->put("enroll.{$enrollment->id}.expire", now()->addHour());

        // Redirect to 'please wait' page
        return response()
            ->view('activities.enrollments.payment-go')
            ->header('Refresh', '0;url=' . route('enroll.pay-wait', compact('activity')))
            ->setPrivate();
    }

    /**
     * Try to start iDEAL.
     *
     * @return RedirectResponse|Response
     * @throws EnrollmentNotFoundException
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function start(Request $request, StripeService $stripeService, Activity $activity)
    {
        // Get enrollment
        $enrollment = $this->findActiveEnrollmentOrFail($request, $activity);

        // Get params for invoice
        $requestBank = $request->session()->get("enroll.{$enrollment->id}.bank");
        $requestExpire = $request->session()->get("enroll.{$enrollment->id}.expire");

        // Redirect if expired
        if ($requestExpire < now() || empty($requestBank)) {
            logger()->warning('The payment request for {enrollment} has expired.', compact('activity', 'enrollment'));

            return response()
                ->redirectToRoute('enroll.show', compact('activity'))
                ->setPrivate();
        }

        // Get the invoice we're going to pay (since we need this for a source to apply)
        $invoice = $stripeService->getInvoice($enrollment);
        logger()->info('Going to pay {invoice}.', compact('invoice'));

        // Create or retireve the source
        $source = $stripeService->getSource($enrollment, $requestBank);
        \assert($source instanceof Source);

        logger()->info('Built source {source}.', compact('source'));

        // Check for redirect
        $redirect = $stripeService->getSourceRedirect($source);
        if ($redirect) {
            logger()->info('And away we go! Redirecting to Stripe.');

            return $redirect;
        }

        // Log it
        logger()->info('Recieved {source}, but it can\'t be paid yet.', compact('source', 'enrollment'));

        // Redirect to 'please wait' page
        return response()
            ->view('activities.enrollments.payment-go')
            ->header('Refresh', '0;url=' . route('enroll.pay-wait', compact('activity')))
            ->setPrivate();
    }

    /**
     * Callback from Stripe.
     *
     * @return Response
     */
    public function complete(Request $request, Activity $activity)
    {
        // Get enrollment
        $enrollment = $this->findActiveEnrollmentOrFail($request, $activity);

        // Redirect to 'please wait' page
        return response()
            ->view('activities.enrollments.payment-complete', [
                'enrollment' => $enrollment,
            ])
            ->header('Refresh', '0;url=' . route('enroll.pay-validate', compact('activity')))
            ->setPrivate();
    }

    /**
     * Perform the actual validation.
     *
     * @return RedirectResponse
     * @throws EnrollmentNotFoundException
     */
    public function completeVerify(
        Request $request,
        StripeServiceContract $service,
        Activity $activity
    ) {
        // Get enrollment
        $enrollment = $this->findActiveEnrollmentOrFail($request, $activity);

        // Check the source
        $source = $service->getSource($enrollment, null);

        // Check if it exists
        if ($source === null) {
            logger()->warning('Cannot find Stripe source for {enrollment}.', [
                'enrollment' => $enrollment,
                'activity' => $activity,
                'user' => $enrollment->user,
                'code' => $enrollment->payment_invoice,
            ]);
            flash('Er is iets bijzonder fout gegaan, probeer het opnieuw.', 'warning');

            return response()
                ->redirectToRoute('activity.show', compact('activity'))
                ->setPrivate();
        }

        if (in_array($source->status, [Source::STATUS_CANCELED, Source::STATUS_FAILED], true)) {
            $result = $source->status === Source::STATUS_CANCELED ? 'geannuleerd' : 'mislukt';
            flash("De betaling voor {$activity->name} is {$result}.", 'info');

            // Redirect to activity
            return response()
                ->redirectToRoute('activity.show', compact('activity'))
                ->setPrivate();
        }

        // We'll retry this bit for 15 seconds
        $timeout = now()->addSeconds(15);

        do {
            // Refresh model
            $enrollment->refresh();

            // Check if paid
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

            // Check if cancelled
            if ($enrollment->state instanceof Cancelled) {
                logger()->notice('Payment received for cancelled invoice', [
                    'enrollment' => $enrollment,
                    'activity' => $activity,
                    'user' => $enrollment->user,
                    'code' => $enrollment->payment_invoice,
                ]);
                flash("Je staat niet meer ingeschreven voor {$activity->name}.", 'warning');

                return response()
                    ->redirectToRoute('activity.show', compact('activity'))
                    ->setPrivate();
            }

            // Wait a bit
            sleep(3);
        } while ($timeout > now());

        // Redirect
        flash('De controle duurt wat lang. Je krijgt een mailtje zodra de betaling is gecontroleerd.', 'info');

        return response()
            ->redirectToRoute('activity.show', compact('activity'))
            ->setPrivate();
    }
}
