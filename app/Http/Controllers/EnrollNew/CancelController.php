<?php

declare(strict_types=1);

namespace App\Http\Controllers\EnrollNew;

use App\Enums\EnrollmentCancellationReason;
use App\Facades\Enroll;
use App\Http\Controllers\Controller;
use App\Http\Middleware\RequireActiveEnrollment;
use App\Jobs\Enrollments\CancelEnrollmentJob;
use App\Models\Activity;
use App\Models\States\Enrollment as States;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Response;

class CancelController extends Controller
{
    public function __construct()
    {
        $this->middleware([
            'auth',
            RequireActiveEnrollment::class,
        ]);
    }

    public function cancel(Activity $activity)
    {
        /** @var Enroll $enrollment */
        $enrollment = Enroll::getEnrollment($activity);
        abort_unless($enrollment, HttpResponse::HTTP_BAD_REQUEST);
        abort_if($enrollment->state instanceof States\Cancelled, HttpResponse::HTTP_BAD_REQUEST);

        // Paid enrollments cannot be cancelled
        if ($enrollment instanceof States\Paid) {
            flash()->warning(__(
                'This enrollment has been paid, and cannot be cancelled.',
            ));

            return Response::redirectToRoute('enroll.show', [$activity]);
        }

        // Cancel enrollment
        CancelEnrollmentJob::dispatch($enrollment, EnrollmentCancellationReason::USER_REQUEST);

        // Flash message
        flash()->success(__(
            "Your enrollment is being cancelled. This might take a bit, but we'll update you via email.",
        ));

        // Redirect
        return Response::redirectToRoute('activity.show', [$activity]);
    }
}
