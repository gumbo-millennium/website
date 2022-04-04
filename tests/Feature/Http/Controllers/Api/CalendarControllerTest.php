<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Facades\Enroll;
use App\Models\Activity;
use App\Models\States\Enrollment as EnrollmentStates;
use App\Models\States\Enrollment\Confirmed;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\URL;
use Sabre\VObject;
use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\Component\VEvent;
use Tests\TestCase;

class CalendarControllerTest extends TestCase
{
    public function test_signature_is_always_required(): void
    {
        $user = User::factory()->create();

        $this->get(route('api.calendar.show', $user))
            ->assertForbidden();

        $this->get(URL::signedRoute('api.calendar.show', $user))
            ->assertOk();

        $this->actingAs($user);

        $this->get(route('api.calendar.show', $user))
            ->assertForbidden();

        $this->get(URL::signedRoute('api.calendar.show', $user))
            ->assertOk();
    }

    public function test_empty_calendar(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(URL::signedRoute('api.calendar.show', $user))
            ->assertOk()
            ->assertDownload();

        $parsedCalendar = VObject\Reader::read($response->getContent());

        $this->assertInstanceOf(VCalendar::class, $parsedCalendar);

        $this->assertNull($parsedCalendar->VEVENT);
    }

    public function test_normal_function(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        /** @var Activity $activity */
        $activity = Activity::factory()->withTickets()->create([
            'start_date' => Date::today()->addDay()->setTime(20, 0),
            'end_date' => Date::today()->addDay()->setTime(23, 0),
        ]);

        /** @var Ticket $ticket */
        $ticket = $activity->tickets->first();

        // Enroll user
        Enroll::createEnrollment($activity, $ticket);

        // Fetch the calendar
        $response = $this->get(URL::signedRoute('api.calendar.show', $user))
            ->assertOk()
            ->assertDownload();

        $parsedCalendar = VObject\Reader::read($response->getContent());

        $this->assertInstanceOf(VCalendar::class, $parsedCalendar);

        $this->assertNotNull($parsedCalendar->VEVENT, 'Failed asserting a VEVENT entry is present in the calendar');
        $this->assertCount(1, $parsedCalendar->VEVENT, 'Failed checking calendar contains a single entry');

        $calendarEvent = $parsedCalendar->VEVENT[0];
        $this->assertInstanceOf(VEvent::class, $calendarEvent, 'Failed fetching first event');

        // Check if the event is properly created
        $this->assertEquals($activity->name, $calendarEvent->SUMMARY, 'Failed asserting event summary is correct');
        $this->assertStringContainsString($ticket->title, (string) $calendarEvent->DESCRIPTION, 'Failed asserting event description contains ticket title');
    }

    public function test_proper_attendance_flag(): void
    {
        [$confirmedActivity, $pendingActivity] = Activity::factory()->withTickets()->createMany([
            [
                'start_date' => Date::today()->addDay()->setTime(20, 0),
                'end_date' => Date::today()->addDay()->setTime(23, 0),
            ],
            [
                'start_date' => Date::today()->addWeek()->setTime(20, 0),
                'end_date' => Date::today()->addWeek()->setTime(23, 0),
            ],
        ]);

        // Get tickets
        $confirmedTicket = $confirmedActivity->tickets->first();
        $pendingTicket = $pendingActivity->tickets->first();

        $pendingTicket->price = 20_00;
        $pendingTicket->save();

        // Create a user
        $user = User::factory()->create();
        $this->actingAs($user);

        // Enroll the user
        $confirmedEnrollment = Enroll::createEnrollment($confirmedActivity, $confirmedTicket);
        $pendingEnrollment = Enroll::createEnrollment($pendingActivity, $pendingTicket);

        // Change the state of the confirmed enrollment
        $confirmedEnrollment->transitionTo(EnrollmentStates\Confirmed::class);

        // Ensure states are correct
        $this->assertTrue($confirmedEnrollment->is_stable, 'Failed asserting confirmed enrollment is stable');
        $this->assertFalse($pendingEnrollment->is_stable, 'Failed asserting pending enrollment is not stable');

        // Fetch the calendar
        $response = $this->get(URL::signedRoute('api.calendar.show', $user))
            ->assertOk()
            ->assertDownload();

        $parsedCalendar = VObject\Reader::read($response->getContent());

        $this->assertInstanceOf(VCalendar::class, $parsedCalendar);

        $this->assertNotNull($parsedCalendar->VEVENT, 'Failed asserting a VEVENT entry is present in the calendar');
        $this->assertCount(2, $parsedCalendar->VEVENT, 'Failed checking calendar contains two entries');

        $confirmedEvent = $parsedCalendar->VEVENT[0];
        $pendingEvent = $parsedCalendar->VEVENT[1];
        $this->assertInstanceOf(VEvent::class, $confirmedEvent, 'Failed fetching confirmed event');
        $this->assertInstanceOf(VEvent::class, $pendingEvent, 'Failed fetching pending event');

        $this->assertInstanceOf(VObject\Property::class, $confirmedEvent->ATTENDEE, 'Failed to find attendee in confirmed event');
        $this->assertInstanceOf(VObject\Property::class, $pendingEvent->ATTENDEE, 'Failed to find attendee in pending event');

        $confirmedAttendance = $confirmedEvent->ATTENDEE[0]->parameters();
        $pendingAttendance = $pendingEvent->ATTENDEE[0]->parameters();

        // Ensure the proper attendance flags are set for the confirmed event
        $this->assertArrayHasKey('PARTSTAT', $confirmedAttendance, 'Failed asserting PARTSTAT parameter is present in confirmed event');
        $this->assertEquals('ACCEPTED', $confirmedAttendance['PARTSTAT'], 'Failed asserting participant status is set to accepted');

        // Do the same for pending
        $this->assertArrayHasKey('PARTSTAT', $pendingAttendance, 'Failed asserting PARTSTAT parameter is present in pending event');
        $this->assertEquals('NEEDS-ACTION', $pendingAttendance['PARTSTAT'], 'Failed asserting participant status is set to needs action');
    }

    public function test_past_events_are_included(): void
    {
        // Create a user
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create activities
        Activity::factory()->withTickets()->createMany([
            [
                'start_date' => Date::today()->addDay()->setTime(20, 0),
                'end_date' => Date::today()->addDay()->setTime(23, 0),
            ],
            [
                'start_date' => Date::today()->addMonth()->setTime(20, 0),
                'end_date' => Date::today()->addMonth()->setTime(23, 0),
            ],
        ])->each(function (Activity $activity) {
            $ticket = $activity->tickets->first();

            $enrollment = Enroll::createEnrollment($activity, $ticket);
            $enrollment->transitionTo(EnrollmentStates\Confirmed::class);
        });

        // Fast forward 5 days
        $this->travel(5)->days();

        // Fetch the calendar
        $response = $this->get(URL::signedRoute('api.calendar.show', $user))
            ->assertOk()
            ->assertDownload();

        $parsedCalendar = VObject\Reader::read($response->getContent());

        $this->assertInstanceOf(VCalendar::class, $parsedCalendar);

        $this->assertNotNull($parsedCalendar->VEVENT, 'Failed asserting a VEVENT entry is present in the calendar');
        $this->assertCount(2, $parsedCalendar->VEVENT, 'Failed checking calendar contains two entries');
    }

    public function test_cancelled_enrollments_are_not_included(): void
    {
        // Create a user
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create activities
        $activity = Activity::factory()->withTickets()->create([
            'start_date' => Date::today()->addWeek()->setTime(20, 0),
            'end_date' => Date::today()->addWeek()->setTime(23, 0),
        ]);

        // Find the ticket
        $ticket = $activity->tickets->first();

        // Enroll and cancel
        $enrollment = Enroll::createEnrollment($activity, $ticket);
        $enrollment->transitionTo(EnrollmentStates\Cancelled::class);

        // Fetch the calendar
        $response = $this->get(URL::signedRoute('api.calendar.show', $user))
            ->assertOk()
            ->assertDownload();

        $parsedCalendar = VObject\Reader::read($response->getContent());

        $this->assertInstanceOf(VCalendar::class, $parsedCalendar);

        $this->assertNull($parsedCalendar->VEVENT, 'Failed asserting a VEVENT entry is mssing from the calendar');
    }
}
