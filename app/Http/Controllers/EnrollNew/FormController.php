<?php

declare(strict_types=1);

namespace App\Http\Controllers\EnrollNew;

use App\Facades\Enroll;
use App\Http\Controllers\Controller;
use App\Http\Middleware\RequireActiveEnrollment;
use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\States\Enrollment as States;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class EnrollmentController extends Controller
{
    public function __construct()
    {
        $this->middleware([
            'auth',
            RequireActiveEnrollment::class,
        ]);
    }

    /**
     * @return HttpResponse|RedirectResponse
     */
    public function edit(Request $request, Activity $activity)
    {
        // User not enrolled, fail
        if (! $enrollment = Enroll::getEnrollment($activity)) {
            flash()->warning(__(
                "You're currently not enrolled into :activity",
                ['activity' => $activity->name],
            ));

            return Response::redirectToRoute('enroll.ticket', [$activity]);
        }

        // No form for this activity, redirect to show page
        if ($activity->form === null) {
            return Response::redirectToRoute('enroll.show', [$activity]);
        }

        if ($enrollment->form === null && $activity->form !== null) {
            return Response::redirectToRoute('enroll.form', [$activity]);
        }

        if ($enrollment->price > 0 && ! $enrollment->state instanceof States\Paid) {
            return Response::redirectToRoute('enroll.pay', [$activity]);
        }

        throw new HttpException(501, 'Not implemented');
    }

    /**
     * Create a new enrollment for the given activity.
     */
    public function update(Request $request, Activity $activity): HttpResponse
    {
        $enrollment = Enroll::getEnrollment($activity);

        if ($enrollment) {
            return Response::redirectToRoute('enroll.show', [$activity]);
        }

        // Get tickets
        $tickets = Enroll::findTicketsForActivity($activity);

        // Done
        return Response::view('enrollments.tickets', [
            'activity' => $activity,
            'tickets' => $tickets,
        ]);
    }
}
