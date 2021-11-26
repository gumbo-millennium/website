<?php

declare(strict_types=1);

namespace App\Http\Controllers\EnrollNew;

use App\Facades\Enroll;
use App\Facades\Payments;
use App\Http\Controllers\Controller;
use App\Http\Middleware\RequireActiveEnrollment;
use App\Http\Middleware\RequirePaidEnrollment;
use App\Models\Activity;
use App\Models\States\Enrollment as States;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware([
            'auth',
            RequireActiveEnrollment::class,
        ]);

        $this->middleware(RequirePaidEnrollment::class)
            ->except(['create']);
    }

    /**
     * Creates or updates a payment to the enrollment.
     * @throws HttpException
     */
    public function create(Request $request, Activity $activity)
    {
        // Find enrollment
        $enrollment = Enroll::getEnrollment($activity);

        // Skip if not paid
        if ($enrollment->price === null) {
            // Check if we need to upgrade from seeded → confirmed
            if ($enrollment->state instanceof States\Seeded) {
                $enrollment->state = new States\Confirmed($enrollment);
                $enrollment->save();
            }

            return Response::redirectToRoute('enroll.show', [$activity]);
        }

        // Get banks
        $banks = Collection::make(Payments::getIdealMethods());

        // Get "highlighted" banks
        $highlightedBanks = $banks->only(Config::get('gumbo.preferred-banks'))->sortKeys();
        $banks = $banks->except(Config::get('gumbo.preferred-banks'))->sortKeys();

        // Show the view
        return Response::view('enrollments.payment', [
            'enrollment' => $enrollment,
            'activity' => $activity,
            'banks' => $banks,
            'highlightedBanks' => $highlightedBanks,
        ]);
    }

    public function store(Request $request, Activity $activity): RedirectResponse
    {
        $validated = $request->validate([
            'bank' => [
                'required',
                'string',
                Rule::in(array_keys(Payments::getIdealMethods())),
            ],
        ]);

        // TODO
        throw new HttpException(501, 'Not implemented');
    }

    /**
     * Show payment, which basically renders a view that redirects to the
     * redirect route that connects to the payment provider, or redirects
     * to the verification route that actively verifies the payment.
     */
    public function show(Request $request, Activity $activity)
    {
        // Find enrollment
        $enrollment = Enroll::getEnrollment($activity);

        // Redirect to details if already paid
        if ($enrollment->state instanceof States\Paid) {
            flash()->success(__(
                'Your payment for :activity has been verified.',
                ['activity' => $activity->name],
            ));

            return Response::redirectToRoute('enroll.show', [$activity]);
        }

        // Redirect to verification if returning from payment
        if ($request->has('verify')) {
            return Response::view('enroll.payment.wait', [
                'activity' => $activity,
                'enrollment' => $enrollment,
                'verify' => true,
            ], 200, [
                'Refresh' => sprintf('0; url=%s', route('enroll.verify', [$activity])),
            ]);
        }

        // Redirect to the route that actually creates the payment
        return Response::view('enroll.payment.wait', [
            'activity' => $activity,
            'enrollment' => $enrollment,
            'verify' => true,
        ], 200, [
            'Refresh' => sprintf('0; url=%s', route('enroll.verify', [$activity])),
        ]);
    }

    public function redirect(Request $request, Activity $activity): RedirectResponse
    {
        // TODO
        throw new HttpException(501, 'Not implemented');
    }

    public function verify(Request $request, Activity $activity): RedirectResponse
    {
        // TODO
        throw new HttpException(501, 'Not implemented');
    }
}
