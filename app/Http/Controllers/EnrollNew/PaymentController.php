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
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Response;
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

        // Show the view
        return Response::view('enrollments.payment', [
            'enrollment' => $enrollment,
            'activity' => $activity,
        ]);
    }

    public function store(Request $request, Activity $activity): RedirectResponse
    {
        // Find enrollment
        $enrollment = Enroll::getEnrollment($activity);

        // Fail if invalid
        abort_if($enrollment->price === null, HttpResponse::HTTP_BAD_REQUEST);
        if ($enrollment->paid_at !== null) {
            return Response::redirectToRoute('enroll.show', [$activity]);
        }

        // Check if the order needs payment
        $payment = $enrollment->payments->first() ?? null;
        if ($payment && $payment->is_stable) {
            return Response::redirectToRoute('enroll.show', [$activity]);
        }

        // Create the order
        if (! $payment) {
            $payment = Payments::create($enrollment);
        }

        // Redirect to 'please wait' page
        return Response::redirectToRoute('payment.show', [$payment]);
    }
}
