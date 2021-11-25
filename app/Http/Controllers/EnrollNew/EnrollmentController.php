<?php

declare(strict_types=1);

namespace App\Http\Controllers\EnrollNew;

use App\Facades\Enroll;
use App\Http\Controllers\Controller;
use App\Http\Middleware\RequireActiveEnrollment;
use App\Models\Activity;
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
        $this->middleware('auth');

        $this->middleware(RequireActiveEnrollment::class)->only(['show']);
    }

    /**
     * Display all enrollments for a user.
     */
    public function index(Request $request): HttpResponse
    {
        throw new HttpException(501, 'Not implemented');
    }

    /**
     * @return HttpResponse|RedirectResponse
     */
    public function show(Request $request, Activity $activity)
    {
        if (! $enrollment = Enroll::getEnrollment($activity)) {
            flash()->warning(__(
                "You're currently not enrolled into :activity",
                ['activity' => $activity->name],
            ));

            return Response::redirectToRoute('enroll.ticket', [$activity]);
        }

        if ($enrollment->form === null && $activity->form !== null) {
            return Response::redirectToRoute('enroll.form', [$activity]);
        }

        if ($enrollment->price > 0 && ! $enrollment->state instanceof States\Paid) {
            return Response::redirectToRoute('enroll.pay', [$activity]);
        }

        throw new HttpException(501, 'Not implemented');
    }
}
