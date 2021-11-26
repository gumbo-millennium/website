<?php

declare(strict_types=1);

namespace App\Http\Controllers\EnrollNew;

use App\Exceptions\EnrollmentFailedException;
use App\Facades\Enroll;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Enrollment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\Rule;

class TicketController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Create a new enrollment for the given activity.
     * @return HttpResponse|RedirectResponse
     */
    public function create(Request $request, Activity $activity)
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

    /**
     * Store the new activity enrollment, which has a ticket.
     */
    public function store(Request $request, Activity $activity): RedirectResponse
    {
        // Get tickets
        $tickets = Collection::make(Enroll::findTicketsForActivity($activity));

        // Validate request
        $valid = $request->validate([
            'ticket_id' => [
                'required',
                Rule::in($tickets->pluck('id')),
            ],
        ]);

        // Find the right ticket
        $ticket = $tickets->firstWhere('id', $valid['ticket_id']);
        abort_unless($ticket, HttpResponse::HTTP_BAD_REQUEST);

        try {
            // Enroll using the given ticket
            $enrollment = Enroll::createEnrollment($activity, $ticket);
        } catch (EnrollmentFailedException $exception) {
            flash()->error(__(
                'Something went wrong enrolling you into :activity, please try again.',
                ['activity' => $activity->name],
            ));

            // Return to the previous page, but explicitly specifying it
            return Response::redirectToRoute('enroll.ticket', [$activity]);
        }

        // Redirect to info, let that thing figure it out
        return Response::redirectToRoute('enroll.show', [$activity]);
    }
}
