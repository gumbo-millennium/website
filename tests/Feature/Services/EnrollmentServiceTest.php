<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Exceptions\EnrollmentFailedException;
use App\Facades\Enroll;
use App\Helpers\Str;
use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\States\Enrollment as States;
use App\Models\User;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use LogicException;
use Tests\TestCase;

class EnrollmentServiceTest extends TestCase
{
    public static function enrollmentOptions(): array
    {
        return [
            'no user' => [false, false],
            'not enrolled' => [false, true, null],
            'enrolled' => [true, true, [
                'state' => States\Seeded::class,
            ]],
            'confirmed' => [true, true, [
                'state' => States\Confirmed::class,
            ]],
            'cancelled' => [false, true, [
                'state' => States\Cancelled::class,
            ]],
            'deleted' => [false, true, fn () => [
                'deleted_at' => now(),
            ]],
        ];
    }

    /**
     * Test the enrollment service.
     * @dataProvider enrollmentOptions
     */
    public function test_get_enrollment_function(bool $shouldMatch, bool $makeUser, array|Closure|null $enrollmentData = null): void
    {
        $user = $makeUser ? User::factory()->create() : null;
        $user and $this->actingAs($user);

        $activity = Activity::factory()->withTickets()->create();
        $ticket = $activity->tickets->first();

        if ($enrollmentData !== null) {
            $enrollment = Enrollment::factory()->make();
            $enrollment->ticket()->associate($ticket);
            $enrollment->user()->associate($user);

            $enrollment->forceFill(value($enrollmentData));

            $activity->enrollments()->save($enrollment);
        }

        if ($user) {
            $this->actingAs($user);
        }

        $foundEnrollment = Enroll::getEnrollment($activity);

        if (! $shouldMatch) {
            $this->assertTrue($foundEnrollment === null, 'Failed asserting no enrollment was returned');

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
        $activity = Activity::factory()->create();

        $seededTickets = $activity->tickets()->createMany([
            // Before tickets
            [
                'title' => 'Before for everyone',
                'available_until' => Date::now()->subHour(),
                'is_public' => true,
            ],
            [
                'title' => 'Before for members',
                'available_until' => Date::now()->subHour(),
                'is_public' => false,
            ],

            // Current tickets
            [
                'title' => 'Current for everyone',
                'available_from' => Date::now()->subHour(),
                'available_until' => Date::now()->addHour(),
                'is_public' => true,
            ],
            [
                'title' => 'Current for members',
                'available_from' => Date::now()->subHour(),
                'available_until' => Date::now()->addHour(),
                'is_public' => false,
            ],

            // After tickets
            [
                'title' => 'After for everyone',
                'available_from' => Date::now()->addHour(),
                'is_public' => true,
            ],
            [
                'title' => 'After for members',
                'available_from' => Date::now()->addHour(),
                'is_public' => false,
            ],
        ]);

        // Get the middle tickets
        $currentPublicTicket = $seededTickets->firstWhere('title', 'Current for everyone');
        $currentMemberTicket = $seededTickets->firstWhere('title', 'Current for members');

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
        $activity = Activity::factory()->create();

        $ticket = $activity->tickets()->create([
            'title' => 'Quantity ticket',
            'quantity' => 2,
        ]);

        [$user1, $user2, $user3] = User::factory(3)->create();

        $this->actingAs($user1);
        $this->assertTrue(Enroll::canEnroll($activity));

        $enrollment1 = Enroll::createEnrollment($activity, $ticket);
        $this->assertInstanceOf(Enrollment::class, $enrollment1);

        $this->actingAs($user2);
        $this->assertTrue(Enroll::canEnroll($activity));

        $enrollment2 = Enroll::createEnrollment($activity, $ticket);
        $this->assertInstanceOf(Enrollment::class, $enrollment2);

        $this->actingAs($user3);
        $this->assertFalse(Enroll::canEnroll($activity));

        // Check count
        $ticket->refresh();
        $this->assertSame(0, $ticket->quantity_available);

        // Transition enrollment
        $enrollment1->state->transitionTo(States\Cancelled::class);
        $enrollment1->save();

        // Re-check count
        $ticket->refresh();
        $this->assertSame(1, $ticket->quantity_available);
        $this->assertTrue(Enroll::canEnroll($activity));
    }

    public function test_public_with_infinite_seats(): void
    {
        $activity = Activity::factory()->create([
            'is_public' => true,
        ]);

        $ticket = $activity->tickets()->create([
            'title' => 'Guest ticket',
            'is_public' => true,
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
        $activity = Activity::factory()->create([
            'is_public' => true,
        ]);

        $activity->tickets()->create([
            'title' => 'Member ticket',
            'is_public' => false,
        ]);

        $this->assertFalse(Enroll::canEnroll($activity));

        $this->actingAs($this->getGuestUser());

        $this->assertFalse(Enroll::canEnroll($activity));

        $this->actingAs($this->getMemberUser());

        $this->assertTrue(Enroll::canEnroll($activity));
    }

    public function test_private_enrollment(): void
    {
        $activity = Activity::factory()->create([
            'is_public' => false,
        ]);

        $activity->tickets()->create([
            'title' => 'Member ticket',
            'is_public' => false,
        ]);

        Auth::logout();

        $this->assertFalse(Enroll::canEnroll($activity), 'Failed asserting guests cannot enroll');

        $this->actingAs($this->getGuestUser());

        $this->assertFalse(Enroll::canEnroll($activity), 'Failed asserting non-members cannot enroll');

        $this->actingAs($this->getMemberUser());

        $this->assertTrue(Enroll::canEnroll($activity), 'Failed asserting members can enroll');
    }

    public function test_activity_with_limited_seats(): void
    {
        $activity = Activity::factory()->withTickets()->create([
            'seats' => 1,
        ]);

        $this->actingAs($user1 = User::factory()->create());
        $this->assertTrue(Enroll::canEnroll($activity));

        $this->assertInstanceOf(Enrollment::class, Enroll::createEnrollment($activity, $activity->tickets()->first()));

        $this->assertCount(1, $user1->enrollments()->get());

        // Check user2 cannot enroll
        $this->actingAs(User::factory()->create());
        $this->assertFalse(Enroll::canEnroll($activity));

        $this->expectException(EnrollmentFailedException::class);
        Enroll::createEnrollment($activity, $activity->tickets()->first());
    }

    /**
     * Test signing up a second time somehow.
     */
    public function test_re_enrollment(): void
    {
        $activity = Activity::factory()->withTickets()->create();
        $ticket = $activity->tickets->first();

        $this->actingAs($user = User::factory()->create());

        $this->assertTrue(Enroll::canEnroll($activity));
        $this->assertInstanceOf(Enrollment::class, Enroll::createEnrollment($activity, $ticket));

        $this->assertCount(1, $user->enrollments()->get());

        $this->assertFalse(Enroll::canEnroll($activity));

        $this->expectException(EnrollmentFailedException::class);
        Enroll::createEnrollment($activity, $ticket);
    }

    /**
     * Test transfering an enrollment to yourself.
     */
    public function test_transfer_to_self(): void
    {
        $activity = Activity::factory()->withTickets()->create();
        $ticket = $activity->tickets->first();

        $user = User::factory()->create();
        $this->actingAs($user);

        // make enrollment
        $enrollment = Enroll::createEnrollment($activity, $ticket);

        // Should pass without token
        $this->assertTrue(Enroll::canTransfer($enrollment), 'Failed asserting the enrollment accepts transfers');

        // Add transfer code
        $enrollment->transfer_secret = Str::random(32);
        $enrollment->save();

        // Transfer to self
        $this->expectException(LogicException::class);
        Enroll::transferEnrollment($enrollment, $user);
    }

    public function test_transfer_cancelled_and_trashed(): void
    {
        $activity = Activity::factory()->withTickets()->create();
        $ticket = $activity->tickets->first();

        $this->actingAs(User::factory()->create());

        // Check the user can enroll
        $this->assertTrue(Enroll::canEnroll($activity), 'Failed asserting the user can enroll into the activity');

        // Enroll user
        $enrollment = Enroll::createEnrollment($activity, $ticket);
        $this->assertInstanceOf(Enrollment::class, $enrollment, 'Failed asserting the enrollment was created');

        // Check the enrollment can transfer
        $this->assertTrue(Enroll::canTransfer($enrollment), 'Failed asserting the enrollment can be transferred');

        // Cancel enrollment
        $this->assertTrue($enrollment->state->canTransitionTo(States\Cancelled::class), 'Failed asserting the enrollment can be cancelled');
        $enrollment = $enrollment->state->transitionTo(States\Cancelled::class)->refresh();

        // Check the enrollment can't transfer anymore
        $this->assertTrue($enrollment->state instanceof States\Cancelled);
        $this->assertFalse(Enroll::canTransfer($enrollment), 'Failed asserting a cancelled enrollment cannot be transferred');

        // Re-enroll
        $this->assertTrue(Enroll::canEnroll($activity), 'Failed asserting the user can re-enroll into the activity');
        $enrollment2 = Enroll::createEnrollment($activity, $ticket);
        $this->assertInstanceOf(Enrollment::class, $enrollment2, 'Failed asserting the enrollment was created');

        // Check the enrollment can transfer again
        $this->assertTrue(Enroll::canTransfer($enrollment2), 'Failed asserting the enrollment can be transferred');

        // Soft-delete the enrollment
        $enrollment2->delete();

        // Check the enrollment can't transfer anymore
        $this->assertFalse(Enroll::canTransfer($enrollment2), 'Failed asserting the enrollment cannot be transferred after being deleted');

        // Ensure we've been using two separate objects
        $this->assertFalse($enrollment->is($enrollment2), 'Failed asserting the two enrollments are different');
    }

    /**
     * Test transfering a proper enrollment.
     */
    public function test_regular_transfer(): void
    {
        $activity = Activity::factory()->withTickets()->create();
        $ticket = $activity->tickets->first();

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $this->actingAs($user1);

        // make enrollment
        $enrollment = Enroll::createEnrollment($activity, $ticket);

        // Should pass without token
        $this->assertTrue(Enroll::canTransfer($enrollment), 'Failed asserting the enrollment accepts transfers');

        // Check counts
        $this->assertCount(1, $user1->enrollments()->get());
        $this->assertCount(0, $user2->enrollments()->get());

        // Add transfer code
        $enrollment->transfer_secret = Str::random(32);
        $enrollment->save();

        // Transfer to self
        $enrollment2 = Enroll::transferEnrollment($enrollment, $user2);
        $this->assertTrue($enrollment2->is($enrollment), 'Failed asserting the same enrollment was returned');

        // Refresh
        $enrollment->refresh();
        $this->assertTrue($enrollment->user->is($user2), 'Failed asserting the enrollment was transferred');

        // Check counts
        $this->assertCount(0, $user1->enrollments()->get());
        $this->assertCount(1, $user2->enrollments()->get());

        // Test enrollment remains transferrable
        $this->assertTrue(Enroll::canTransfer($enrollment), 'Failed asserting the enrollment accepts transfers after a transfer');

        // Test it loses its transfer code
        $this->assertNull($enrollment->transfer_secret, 'Failed asserting the enrollment transfer code is wiped after handover');
    }

    public function test_transfer_unstable_enrollment(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $activity = Activity::factory()->create();

        $enrollment = $user1->enrollments()->make([
            'state' => States\Seeded::class,
        ]);
        $enrollment->activity()->associate($activity);
        $enrollment->transfer_secret = Str::random(32);
        $enrollment->save();

        $this->assertTrue(Enroll::canTransfer($enrollment), 'Failed asserting the enrollment can be transferred');
        $this->assertNotNull($enrollment->expire, 'Failed asserting that the enrollment was assigned expiry');

        $beforeExpiration = $enrollment->expire;

        Date::setTestNow(Date::now()->addMinutes(35));

        Enroll::transferEnrollment($enrollment, $user2);

        $enrollment->refresh();

        $this->assertGreaterThanOrEqual(
            $beforeExpiration,
            $enrollment->expire,
            'Failed asserting that the enrollment expiration was updated',
        );
    }

    public function test_transfer_stable_enrollment(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $activity = Activity::factory()->create();

        $enrollment = $user1->enrollments()->make([
            'state' => States\Confirmed::class,
        ]);
        $enrollment->activity()->associate($activity);
        $enrollment->transfer_secret = Str::random(32);
        $enrollment->save();

        $this->assertTrue($user1->is($enrollment->user), 'Failed assserting user1 is the owner');

        Enroll::transferEnrollment($enrollment, $user2);

        $enrollment->refresh();

        $this->assertFalse($user1->is($enrollment->user), 'Failed assserting user1 is no longer the owner');
        $this->assertTrue($user2->is($enrollment->user), 'Failed asserting user2 is the owner');

        $this->assertNull($enrollment->transfer_secret, 'Failed asserting transfer secret is cleared');
    }

    /**
     * Check if enrollment states and consumption state have the proper impact on the
     * transferability of the enrollment.
     */
    public function test_transfer_ability(): void
    {
        $activity = Activity::factory()->withTickets()->create();
        $ticket = $activity->tickets->first();

        $factory = Enrollment::factory()->for($activity)->for($ticket)->has(User::factory());
        $validEnrollment = $factory->create([
            'state' => States\Confirmed::class,
        ]);
        $createdEnrollment = $factory->create([
            'state' => States\Created::class,
        ]);
        $cancelledEnrollment = $factory->create([
            'state' => States\Cancelled::class,
        ]);
        $consumedEnrollment = $factory->create([
            'state' => States\Confirmed::class,
            'consumed_at' => Date::now(),
        ]);
        $trashedEnrollment = $factory->create([
            'state' => States\Confirmed::class,
        ]);
        $trashedEnrollment->delete();

        $this->assertTrue(Enroll::canTransfer($validEnrollment), 'Failed asserting a confirmed, clean enrollment can be transferred');
        $this->assertTrue(Enroll::canTransfer($createdEnrollment), 'Failed asserting a created enrollment cannot be transferred');
        $this->assertFalse(Enroll::canTransfer($cancelledEnrollment), 'Failed asserting a cancelled enrollment cannot be transferred');
        $this->assertFalse(Enroll::canTransfer($consumedEnrollment), 'Failed asserting a consumed enrollment cannot be transferred');
        $this->assertFalse(Enroll::canTransfer($trashedEnrollment), 'Failed asserting a trashed enrollment cannot be transferred');

        $activity->start_date = Date::now()->subHour();
        $activity->end_date = Date::now()->addHour();
        $activity->save();

        $this->assertFalse(Enroll::canTransfer($validEnrollment->fresh()), 'Failed asserting a confirmed, cleanenrollment cannot be transferred after event start');
        $this->assertFalse(Enroll::canTransfer($createdEnrollment->fresh()), 'Failed asserting a created enrollment cannot be transferred after event start');
    }

    public function test_can_enroll_failure_cases(): void
    {
        $activity = Activity::factory()->withTickets()->create([
            'seats' => 1,
        ]);

        $user = User::factory()->create();
        $user2 = User::factory()->create();

        // Test ticket unavailability
        $ticket = $activity->tickets()->first();
        $ticket->available_from = Date::now()->addDays(1);
        $ticket->save();

        $this->assertFalse(Enroll::canEnroll($activity, $user));

        // Test seat availability
        $ticket->available_from = null;
        $ticket->save();
    }

    public function test_permissions_on_started_and_ended_activities(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $activity = Activity::factory()->withTickets()->create([
            'start_date' => Date::now()->addDay(),
            'end_date' => Date::now()->addDay()->addHour(),
        ]);
        $ticket = $activity->tickets->first();

        // Create enrollment under user1
        $this->actingAs($user1);
        $this->assertInstanceOf(Enrollment::class, $enrollment = Enroll::createEnrollment($activity, $ticket));

        // Created, switch to user2
        $this->actingAs($user2);

        $this->assertTrue(Enroll::canEnroll($activity), 'Failed asserting users can enroll before start');
        $this->assertTrue(Enroll::canTransfer($enrollment), 'Failed asserting users can transfer before start');

        $activity->forceFill([
            'start_date' => Date::now()->subDay(),
            'end_date' => Date::now()->addDay(),
        ])->save();
        $enrollment->refresh();

        $this->assertTrue(Enroll::canEnroll($activity), 'Failed asserting users can enroll after start');
        $this->assertFalse(Enroll::canTransfer($enrollment), 'Failed asserting users cannot transfer after start');

        $activity->forceFill([
            'start_date' => Date::now()->subDay(),
            'end_date' => Date::now()->subDay(),
        ])->save();
        $enrollment->refresh();

        $this->assertFalse(Enroll::canEnroll($activity), 'Failed asserting users cannot enroll after end');
        $this->assertFalse(Enroll::canTransfer($enrollment), 'Failed asserting users cannot transfer after end');
    }
}
