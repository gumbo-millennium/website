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
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\Rule;
use RuntimeException;

class TicketController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'can:view,activity']);
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

        // Check if ended
        if ($redirect = $this->redirectIfEnded($activity)) {
            return $redirect;
        }

        // Get user
        $user = $request->user();

        // Get all tickets and all available tickets
        $availableTickets = Enroll::findTicketsForActivity($activity);
        $allTickets = $activity->tickets;

        // Store available and then unavailable
        $ticketSets = [[], []];

        // Find all available tickets
        foreach ($allTickets as $ticket) {
            // Ticket is for sale
            if ($availableTickets->contains($ticket)) {
                $ticketSets[0][] = $ticket;

                continue;
            }

            // Ticket is not for sale and not visible to the user
            if ((! $user?->is_member) && $ticket->members_only) {
                continue;
            }

            // Ticket is not for sale, but show it anyway
            $ticketSets[1][] = $ticket;
        }

        // Done
        return Response::view('enrollments.tickets', [
            'activity' => $activity,
            'hasTickets' => $availableTickets->isNotEmpty(),
            'tickets' => Collection::make($ticketSets)->collapse(),
        ]);
    }

    /**
     * Store the new activity enrollment, which has a ticket.
     */
    public function store(Request $request, Activity $activity): RedirectResponse
    {
        // Redirect if enrolled
        if (Enroll::getEnrollment($activity)) {
            return Response::redirectToRoute('enroll.show', [$activity]);
        }

        // Check if ended
        if ($redirect = $this->redirectIfEnded($activity)) {
            return $redirect;
        }

        // Get tickets
        $tickets = Enroll::findTicketsForActivity($activity);

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
            Enroll::createEnrollment($activity, $ticket);
        } catch (EnrollmentFailedException $exception) {
            report(new RuntimeException(sprintf(
                'Failed to enroll user %s in activity %s',
                $request->user()?->id,
                $activity->id,
            ), 0, $exception));

            flash()->error(__(
                'Something went wrong enrolling you into :activity, please try again.',
                ['activity' => $activity->name],
            ));

            // Return to the previous page, but explicitly specifying it
            return Response::redirectToRoute('enroll.create', [$activity]);
        }

        // Redirect to info, let that thing figure it out
        return Response::redirectToRoute('enroll.show', [$activity]);
    }

    private function redirectIfEnded(Activity $activity): ?RedirectResponse
    {
        // Check if ended
        if ($activity->end_date < Date::now()) {
            flash()
                ->warning(__('You cannot enroll for an activity that has ended.'));

            return Response::redirectToRoute('activity.show', [$activity]);
        }

        return null;
    }
}
