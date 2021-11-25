<?php

declare(strict_types=1);

namespace App\Http\Controllers\EnrollNew;

use App\Facades\Enroll;
use App\Http\Controllers\Controller;
use App\Http\Middleware\RequireActiveEnrollment;
use App\Http\Middleware\RequirePaidEnrollment;
use App\Models\Activity;
use App\Models\States\Enrollment as States;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware([
            'auth',
            RequireActiveEnrollment::class,
            RequirePaidEnrollment::class,
        ]);
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

        // Skip if not paid
        if ($enrollment->price === null) {
            return Response::redirectToRoute('enroll.show', [$activity]);
        }

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

    /**
     * Creates or updates a payment to the enrollment.
     * @throws HttpException
     */
    public function edit(Request $request, Activity $activity)
    {
        // TODO
        throw new HttpException(501, 'Not implemented');
    }

    public function update(Request $request, Activity $activity): RedirectResponse
    {
        // TODO
        throw new HttpException(501, 'Not implemented');
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
