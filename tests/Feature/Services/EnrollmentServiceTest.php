<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Facades\Enroll;
use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\States\Enrollment as States;
use App\Models\User;
use Illuminate\Support\Facades\Date;
use LogicException;
use Tests\TestCase;

class EnrollmentServiceTest extends TestCase
{
    /**
     * Test the enrollment service.
     * @dataProvider enrollmentOptions
     */
    public function test_get_enrollment_function(bool $shouldMatch, bool $makeUser, ?array $enrollmentData = null): void
    {
        $user = $makeUser ? factory(User::class)->create() : null;

        $activity = factory(Activity::class)->create();

        if ($enrollmentData !== null) {
            $enrollment = factory(Enrollment::class)->make();
            $enrollment->forceFill($enrollmentData);

            $enrollment->user()->associate($user);

            $activity->enrollments()->save($enrollment);
        }

        if ($user) {
            $this->actingAs($user);
        }

        $foundEnrollment = Enroll::getEnrollment($activity);

        if (! $shouldMatch) {
            $this->assertNull($foundEnrollment);

            return;
        }

        if ($enrollmentData === null) {
            throw new LogicException('Enrollment data unset but expected to find an enrollment');
        }

        $this->isInstanceOf(Enrollment::class, $foundEnrollment);
        $this->assertTrue($enrollment->is($foundEnrollment), "Returned enrollment isn't the expected enrollment");
    }

    public function test_ticket_availability(): void
    {
        $activity = factory(Activity::class)->create();

        [$beforePublicTicket, $beforeMemberTicket] = $activity->tickets()->createMany([
            [
                'title' => 'Before for everyone',
                'available_until' => Date::now()->subHour(),
                'members_only' => false,
            ],
            [
                'title' => 'Before for members',
                'available_until' => Date::now()->subHour(),
                'members_only' => true,
            ],
        ]);

        [$currentPublicTicket, $currentMemberTicket] = $activity->tickets()->createMany([
            [
                'title' => 'Current for everyone',
                'available_from' => Date::now()->subHour(),
                'available_until' => Date::now()->addHour(),
                'members_only' => false,
            ],
            [
                'title' => 'Current for members',
                'available_from' => Date::now()->subHour(),
                'available_until' => Date::now()->addHour(),
                'members_only' => true,
            ],
        ]);

        [$afterPublicTicket, $afterMemberTicket] = $activity->tickets()->createMany([
            [
                'title' => 'After for everyone',
                'available_from' => Date::now()->addHour(),
                'members_only' => false,
            ],
            [
                'title' => 'After for members',
                'available_from' => Date::now()->addHour(),
                'members_only' => true,
            ],
        ]);

        // Try with guest
        $tickets = Enroll::findTicketsForActivity($activity);

        $this->assertCount(1, $tickets);
        $this->assertTrue($currentPublicTicket->is($tickets[0]), 'Guest should get the current public ticket');

        // Try with user, non-member
        $this->actingAs($this->getGuestUser());

        $tickets = Enroll::findTicketsForActivity($activity);

        $this->assertCount(1, $tickets);
        $this->assertTrue($currentPublicTicket->is($tickets[0]), 'Guest should get the current public ticket');

        // Try with member
        $this->actingAs($this->getMemberUser());

        $tickets = Enroll::findTicketsForActivity($activity);

        $this->assertCount(2, $tickets);
        $this->assertTrue($currentPublicTicket->is($tickets[0]), 'Members should get the current public ticket');
        $this->assertTrue($currentMemberTicket->is($tickets[1]), 'Members should get the current member ticket');
    }

    public function test_ticket_quantity(): void
    {
        $activity = factory(Activity::class)->create();

        $ticket = $activity->tickets()->create([
            'title' => 'Quantity ticket',
            'quantity' => 2,
        ]);

        $user = factory(User::class)->create()->enrollments();
    }

    public function test_public_with_infinite_seats(): void
    {
        $activity = factory(Activity::class)->create([
            'is_public' => true,
        ]);

        $ticket = $activity->tickets()->create([
            'title' => 'Guest ticket',
            'members_only' => false,
        ]);

        // Guest
        $this->assertTrue(Enroll::canEnroll($activity));

        $ticketOptions = Enroll::findTicketsForActivity($activity);
        $this->assertCount(1, $ticketOptions);
        $this->assertTrue($ticket->is($ticketOptions[0]));

        // Logged in user
        $this->actingAs($this->getGuestUser());

        $this->assertTrue(Enroll::canEnroll($activity));

        $ticketOptions = Enroll::findTicketsForActivity($activity);
        $this->assertCount(1, $ticketOptions);
        $this->assertTrue($ticket->is($ticketOptions[0]));

        // Logged in member
        $this->actingAs($this->getMemberUser());

        $this->assertTrue(Enroll::canEnroll($activity));

        $ticketOptions = Enroll::findTicketsForActivity($activity);
        $this->assertCount(1, $ticketOptions);
        $this->assertTrue($ticket->is($ticketOptions[0]));
    }

    public function test_public_with_no_public_tickets(): void
    {
        $activity = factory(Activity::class)->create([
            'is_public' => true,
        ]);

        $activity->tickets()->create([
            'title' => 'Member ticket',
            'members_only' => true,
        ]);

        $this->assertFalse(Enroll::canEnroll($activity));

        $this->actingAs($this->getGuestUser());

        $this->assertFalse(Enroll::canEnroll($activity));

        $this->actingAs($this->getMemberUser());

        $this->assertTrue(Enroll::canEnroll($activity));
    }

    public function test_private_enrollment(): void
    {
        $activity = factory(Activity::class)->create([
            'is_public' => false,
        ]);

        $activity->tickets()->create([
            'title' => 'Member ticket',
            'members_only' => true,
        ]);

        $this->assertFalse(Enroll::canEnroll($activity));

        $this->actingAs($this->getGuestUser());

        $this->assertFalse(Enroll::canEnroll($activity));

        $this->actingAs($this->getMemberUser());

        $this->assertTrue(Enroll::canEnroll($activity));
    }

    public function enrollmentOptions(): array
    {
        return [
            'no user' => [false, false],
            'not enrolled' => [false, true, null],
            'enrolled' => [false, true, [
                'state' => States\Confirmed::class,
            ]],
            'confirmed' => [false, true, [
                'state' => States\Confirmed::class,
            ]],
            'cancelled' => [false, true, [
                'state' => States\Cancelled::class,
            ]],
            'deleted' => [false, true, [
                'deleted_at' => now(),
            ]],
        ];
    }
}
