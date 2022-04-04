<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Helpers\Str;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\States\Enrollment as EnrollmentStates;
use App\Models\User;
use DateInterval;
use DOMDocument;
use DOMElement;
use DOMXPath;
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
use Eluceo\iCal\Presentation\Component\Property\Value\TextValue;
use Eluceo\iCal\Presentation\Component\Property\Value\UriValue;
use Eluceo\iCal\Presentation\Factory\CalendarFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\URL;

class CalendarController extends Controller
{
    /**
     * Creates a clean body text for use in plain text formats (like the calendar entry), by
     * stripping HTML tags and reformatting titles.
     */
    public static function createCleanBodyText(Activity $activity): string
    {
        return Cache::remember("activity.{$activity->id}.clean_body_text", Date::now()->addHour(), function () use ($activity) {
            // Prep document
            $doc = new DOMDocument();

            // Load HTML
            $doc->loadHTML(
                "<article>{$activity->description_html}</article>",
                LIBXML_HTML_NOIMPLIED | LIBXML_NOERROR | LIBXML_NOWARNING,
            );

            // Prep xpath
            $xpath = new DOMXPath($doc);

            // Prep body text
            $bodyLines = [];

            // Iterate over all items, only preserving headers and paragraphs
            foreach ($xpath->query('//div[contains(@class, "container")]/*') as $childNode) {
                if (! $childNode instanceof DOMElement) {
                    continue;
                }

                // Get clean contents
                $asciiValue = Str::of($childNode->nodeValue)->ascii('nl')->trim();

                // Format h1/h2 as main title
                if ($childNode->nodeName === 'h1' || $childNode->nodeName === 'h2') {
                    $bodyLines[] = $asciiValue->upper();
                    $bodyLines[] = str_repeat('=', $asciiValue->length());
                    $bodyLines[] = '';

                    continue;
                }

                // Format h3 as subtitle
                if ($childNode->nodeName === 'h3') {
                    $bodyLines[] = $asciiValue->title();
                    $bodyLines[] = str_repeat('-', $asciiValue->length());
                    $bodyLines[] = '';

                    continue;
                }

                // Format other headers and paragraphs as plain text
                if (preg_match('/^(p|h\d)$/i', $childNode->nodeName)) {
                    $bodyLines[] = $asciiValue;
                    $bodyLines[] = '';

                    continue;
                }

                // All other HTML types are skipped
            }

            // Join bodylines and re-trim
            return trim(implode("\n", $bodyLines));
        });
    }

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
        // Collect events
        $openAdmissionEvents = $this->getOpenEventsForUser($user);
        $enrolledEvents = $this->getEnrolledEventsForUser($user);

        // Add events to an array and sort by start date
        $events = Collection::make([$openAdmissionEvents, $enrolledEvents])
            ->collapse()
            ->sort(function (Event $eventA, Event $eventB) {
                $occurrenceA = $eventA->getOccurrence();
                $occurrenceB = $eventB->getOccurrence();

                if ($occurrenceA instanceof TimeSpan && $occurrenceB instanceof TimeSpan) {
                    return $occurrenceA->getBegin()->getDateTime()->getTimestamp() <=> $occurrenceB->getBegin()->getDateTime()->getTimestamp();
                }
                if ($occurrenceA instanceof Timespan) {
                    return -1;
                }
                if ($occurrenceB instanceof Timespan) {
                    return 1;
                }
            });

        // Create the calendar
        $calendar = new Calendar($events->values()->all());
        $calendarComponent = (new CalendarFactory())->createCalendar($calendar);

        // Create the calendar text name
        $calendarNameString = Str::of("Activiteiten-agenda van {$user->public_name}")->ascii('nl');

        // Add update interval to ensure Google fetches this data a bit often
        $calendarName = new TextValue((string) $calendarNameString);
        $calendarDescription = new TextValue(<<<TEXT
        Persoonlijke agenda van {$user->first_name} met je inschrijvingen en een overzicht
        van activiteiten waar je zonder inschrijven naartoe kan.

        Veel plezier bij de activiteiten van Gumbo!
        TEXT);
        $calendarTimezone = new TextValue(Config::get('app.timezone'));
        $calendarUrl = new UriValue(
            new Uri(URL::signedRoute('api.calendar.show', $user)),
        );
        $calendarUpdateInterval = new DurationValue(new DateInterval('PT12H'));

        $calendarComponent
            // Set name
            ->withProperty(new Property('NAME', $calendarName))
            ->withProperty(new Property('X-WR-CALNAME', $calendarName))

            // Set description
            ->withProperty(new Property('DESCRIPTION', $calendarDescription))
            ->withProperty(new Property('X-WR-CALDESC', $calendarDescription))

            // Set timezone
            ->withProperty(new Property('TIMEZONE-ID', $calendarTimezone))
            ->withProperty(new Property('X-WR-TIMEZONE', $calendarTimezone))

            // Set URL
            ->withProperty(new Property('URL', $calendarUrl))

            // Set TTL
            ->withProperty(new Property('X-PUBLISHED-TTL', $calendarUpdateInterval))
            ->withProperty(new Property('REFRESH-INTERVAL', $calendarUpdateInterval));

        // Send response
        return Response::make($calendarComponent)->withHeaders([
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => sprintf(
                'attachment; filename="%s.ics"',
                $calendarNameString->replace('"', "'"),
            ),
        ]);
    }

    /**
     * @return Event[]
     */
    private function getOpenEventsForUser(User $user): array
    {
        // Find all enrollments for this user that are confirmed or pending payment
        /** @var Activity[] $activities */
        $activities = Activity::query()
            ->where('start_date', '>', Date::today()->subMonth())
            ->whereAvailable($user)
            ->doesntHave('tickets')
            ->get();

        // List of iCal calendar items
        $events = [];

        // Iterate over each enrollment
        foreach ($activities as $activity) {
            $events[] = $this->createCalendarEventFromActivity($activity);
        }

        return $events;
    }

    /**
     * @return Event[]
     */
    private function getEnrolledEventsForUser(User $user): array
    {
        // Find all enrollments for this user that are confirmed or pending payment
        /** @var Enrollment[] $enrollments */
        $enrollments = $user->enrollments()
            ->whereHas('activity', fn (Builder $query) => $query->where('start_date', '>', Date::today()->subYear()))
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

        // Iterate over each enrollment
        foreach ($enrollments as $enrollment) {
            $events[] = $this->createCalendarEventFromEnrollment($enrollment);
        }

        return $events;
    }

    private function createCalendarEventFromActivity(Activity $activity): Event
    {
        // Create Location model
        $location = new Location(
            $activity->location_address ?? $activity->location,
            $activity->location,
        );

        return (new Event())
            ->setSummary($activity->name)
            ->setDescription($this->createCleanBodyText($activity))
            ->setLocation($location)
            ->setUrl(
                new Uri(route('activity.show', $activity)),
            )
            ->setLastModified(
                new Timestamp($activity->updated_at),
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

    /**
     * Create an event for the given activity enrollment.
     */
    private function createCalendarEventFromEnrollment(Enrollment $enrollment): Event
    {
        // Get models
        $activity = $enrollment->activity;
        $ticket = $enrollment->ticket;
        $user = $enrollment->user;

        // Get enrollment values
        $eventPrice = Str::price($enrollment->total_price) ?? __('Free');

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

        // Get event
        $event = $this->createCalendarEventFromActivity($activity);

        // Update description
        $updatedDescription = <<<DESC
        Ticket: {$ticket->title}
        Prijs: {$eventPrice}

        ---

        {$event->getDescription()}
        DESC;

        return $event
            ->setDescription($updatedDescription)
            ->addAttendee($attendee)
            ->setLastModified(
                new Timestamp(
                    $enrollment->updated_at
                        ->max($activity->updated_at)
                        ->max($ticket->updated_at)
                        ->max(Date::createFromTimestamp(filemtime(__FILE__))),
                ),
            );
    }
}
