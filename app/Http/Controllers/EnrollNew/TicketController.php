<?php

declare(strict_types=1);

namespace App\Http\Controllers\EnrollNew;

use App\Exceptions\EnrollmentFailedException;
use App\Facades\Enroll;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\States\Enrollment as States;
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
     */
    public function create(Request $request, Activity $activity): HttpResponse
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

        // Enroll using the given ticket
        try {
            $enrollment = Enroll::createEnrollment($activity, $ticket);
        } catch (EnrollmentFailedException $exception) {
            flash()->error(__(
                'Something went wrong enrolling you into :activity, please try again.',
                ['activity' => $activity->name],
            ));

            return Response::redirectToRoute('enroll.ticket', [$activity]);
        }

        // Flash success message
        flash()->success(__(
            "You're now enrolled into :activity for the next 15 minutes.",
            ['activity' => $activity->name],
        ));

        // Check if a form is required
        if ($activity->form !== null) {
            return Response::redirectToRoute('enroll.form', [$activity]);
        }

        // Transition across the seeded state
        $enrollment->transitionTo(States\Seeded::class);
        $enrollment->save();

        // Check if we need payment
        if ($enrollment->price > 0) {
            return Response::redirectToRoute('enroll.pay', [$activity]);
        }

        // No payment required, enrollment is done
        flash()->success(__(
            "You're now enrolled into :activity.",
            ['activity' => $activity->name],
        ));

        // Transition across the seeded state
        $enrollment->transitionTo(States\Confirmed::class);
        $enrollment->save();

        // Redirect to info
        return Response::redirectToRoute('enroll.show', [$activity]);
    }
}
