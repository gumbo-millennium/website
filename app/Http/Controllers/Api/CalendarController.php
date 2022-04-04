<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Helpers\Str;
use App\Http\Controllers\Controller;
use App\Models\States\Enrollment as EnrollmentStates;
use App\Models\User;
use DateInterval;
use Eluceo\iCal\Domain\Entity\Attendee;
use Eluceo\iCal\Domain\Entity\Calendar;
use Eluceo\iCal\Domain\Entity\Event;
use Eluceo\iCal\Domain\Enum\CalendarUserType;
use Eluceo\iCal\Domain\Enum\ParticipationStatus;
use Eluceo\iCal\Domain\Enum\RoleType;
use Eluceo\iCal\Domain\ValueObject\DateTime;
use Eluceo\iCal\Domain\ValueObject\EmailAddress;
use Eluceo\iCal\Domain\ValueObject\Location;
use Eluceo\iCal\Domain\ValueObject\Organizer;
use Eluceo\iCal\Domain\ValueObject\TimeSpan;
use Eluceo\iCal\Domain\ValueObject\Timestamp;
use Eluceo\iCal\Domain\ValueObject\Uri;
use Eluceo\iCal\Presentation\Component\Property;
use Eluceo\iCal\Presentation\Component\Property\Value\DurationValue;
use Eluceo\iCal\Presentation\Factory\CalendarFactory;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Response;

class CalendarController extends Controller
{
    /**
     * Ensure all requests to this controller are signed.
     */
    public function __construct()
    {
        $this->middleware(['signed']);
    }

    /**
     * Display the user's calendar.
     */
    public function show(User $user): HttpResponse
    {
        // Find all enrollments for this user that are confirmed or pending payment
        /** @var \App\Models\Enrollment[] $enrollments */
        $enrollments = $user->enrollments()
            ->whereState('state', [
                EnrollmentStates\Created::class,
                EnrollmentStates\Seeded::class,
                EnrollmentStates\Confirmed::class,
                EnrollmentStates\Paid::class,
            ])
            ->with(['activity', 'ticket'])
            ->get();

        // List of iCal calendar items
        $events = [];

        // Get the last update time of this file, causing updates to this file
        // to be reflected in user's calendars (if we add/remove fields)
        $fileModifiedTimestamp = Date::createFromTimestamp(filemtime(__FILE__));

        // Iterate over each enrollment
        foreach ($enrollments as $enrollment) {
            // Get models
            $activity = $enrollment->activity;
            $ticket = $enrollment->ticket;

            // Get body text
            $descriptionAsText = strip_tags($activity->description_html ?? '');
            $eventPrice = Str::price($enrollment->total_price) ?? __('Free');
            $eventDescription = trim(<<<DESC
            Ticket: {$ticket->title}
            Prijs: {$eventPrice}

            {$descriptionAsText}
            DESC);

            // Create Location model
            $location = new Location(
                $activity->location_address ?? $activity->location,
                $activity->location,
            );

            // Create Attendee model
            $attendee = (new Attendee(
                new EmailAddress($user->email),
            ))
                ->setRole(RoleType::REQ_PARTICIPANT())
                ->setDisplayName($user->display_name ?? $user->first_name ?? $user->email)
                ->setParticipationStatus(
                    $enrollment->is_stable
                        ? ParticipationStatus::ACCEPTED()
                        : ParticipationStatus::NEEDS_ACTION(),
                )
                ->setResponseNeededFromAttendee(false)
                ->setCalendarUserType(CalendarUserType::INDIVIDUAL());

            $events[] = (new Event())
                ->setSummary($activity->name)
                ->setDescription($eventDescription)
                ->setLocation($location)
                ->addAttendee($attendee)
                ->setUrl(
                    new Uri(route('activity.show', $activity)),
                )
                ->setLastModified(
                    new Timestamp(
                        $enrollment->updated_at
                            ->max($activity->updated_at)
                            ->max($ticket->updated_at)
                            ->max($fileModifiedTimestamp),
                    ),
                )
                ->setOrganizer(
                    (new Organizer(
                        new EmailAddress(Config::get('mail.from.address')),
                        $activity->organiser ?? Config::get('app.name'),
                    )),
                )
                ->setOccurrence(
                    TimeSpan::create(
                        new DateTime($activity->start_date, false),
                        new DateTime($activity->end_date, false),
                    ),
                );
        }

        // Create the calendar
        $calendar = new Calendar($events);
        $calendarComponent = (new CalendarFactory())->createCalendar($calendar);

        // Add update interval to ensure Google fetches this data a bit often
        $updateInterval = new DurationValue(new DateInterval('PT12H'));
        $calendarComponent
            ->withProperty(new Property('X-PUBLISHED-TTL', $updateInterval))
            ->withProperty(new Property('REFRESH-INTERVAL', $updateInterval));

        // Send response
        return Response::make($calendarComponent)->withHeaders([
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => sprintf(
                'attachment; filename="%s.ics"',
                Str::of("Activiteiten-agenda van {$user->public_name}")->ascii('nl')->replace('"', "'"),
            ),
        ]);
    }
}
