<?php

declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Facades\Enroll;
use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\States\Enrollment as States;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Tests\TestCase;

class TicketTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_computed_properties()
    {
        $activity = Activity::factory()->create();

        /** @var Ticket $ticket */
        $ticket = $activity->tickets()->create([
            'title' => 'Test',
        ]);

        $this->assertInstanceOf(Ticket::class, $ticket);

        $this->assertNull($ticket->available_from);
        $this->assertNull($ticket->available_until);
        $this->assertTrue($ticket->is_being_sold);

        $this->assertNull($ticket->quantity);
        $this->assertEquals(0, $ticket->quantity_sold);
        $this->assertNull($ticket->quantity_available);
    }

    public function test_availability(): void
    {
        $activity = Activity::factory()->create();

        /** @var Ticket $ticket */
        [$beforeTicket, $currentTicket, $afterTicket] = $activity->tickets()->createMany([
            [
                'title' => 'Sold before',
                'available_until' => Date::now()->subHour(),
            ],
            [
                'title' => 'Sold now',
                'available_from' => Date::now()->subHour(),
                'available_until' => Date::now()->addHour(),
            ],
            [
                'title' => 'Sold after',
                'available_from' => Date::now()->addHour(),
            ],
        ]);

        $this->assertInstanceOf(Ticket::class, $beforeTicket);
        $this->assertInstanceOf(Ticket::class, $currentTicket);
        $this->assertInstanceOf(Ticket::class, $afterTicket);

        $this->assertNull($beforeTicket->available_from);
        $this->assertNotNull($beforeTicket->available_until);
        $this->assertFalse($beforeTicket->is_being_sold);

        $this->assertNotNull($currentTicket->available_from);
        $this->assertNotNull($currentTicket->available_until);
        $this->assertTrue($currentTicket->is_being_sold);

        $this->assertNotNull($afterTicket->available_from);
        $this->assertNull($afterTicket->available_until);
        $this->assertFalse($afterTicket->is_being_sold);
    }

    public function test_publicness_is_inherited_from_activity(): void
    {
        $activity = Activity::factory()->create([
            'is_public' => false,
        ]);

        /** @var Ticket $ticket */
        $ticket = $activity->tickets()->create([
            'title' => 'Test',
            'is_public' => true,
        ]);

        // Is public is form the DB
        $this->assertTrue($ticket->is_public);

        // Members only is computed
        $this->assertTrue($ticket->members_only);
    }

    public function test_ticket_quantities(): void
    {
        $activity = Activity::factory()->create();

        /** @var Ticket $ticket */
        [$ticketWithoutLimit, $ticketWithLimit] = $activity->tickets()->createMany([
            Ticket::factory()->make(['quantity' => null])->toArray(),
            Ticket::factory()->make(['quantity' => 10])->toArray(),
        ]);

        // Create the proper number of users
        $usersWithWithoutLimitTicket = User::factory()->times(15)->create();
        $usersWithLimitTicket = User::factory()->times(8)->create();

        // Create a bunch of enrollments
        /** @var Collection<Enrollment> $enrollmentsWithoutLimit */
        $enrollmentsWithoutLimit = $usersWithWithoutLimitTicket->map(function ($user) use ($activity, $ticketWithoutLimit) {
            $this->actingAs($user);

            return Enroll::createEnrollment($activity, $ticketWithoutLimit);
        });

        /** @var Collection<Enrollment> $enrollmentsWithLimit */
        $enrollmentsWithLimit = $usersWithLimitTicket->map(function ($user) use ($activity, $ticketWithLimit) {
            $this->actingAs($user);

            return Enroll::createEnrollment($activity, $ticketWithLimit);
        });

        // Cancel 5 without limit
        $enrollmentsWithoutLimit->random(5)->each(function (Enrollment $enrollment) {
            $enrollment->state->transitionTo(States\Cancelled::class);
            $enrollment->save();
        });

        // Cancel 3 with limit
        $enrollmentsWithLimit->random(3)->each(function (Enrollment $enrollment) {
            $enrollment->state->transitionTo(States\Cancelled::class);
            $enrollment->save();
        });

        // Check counts on both
        $this->assertSame(null, $ticketWithoutLimit->quantity);
        $this->assertSame(null, $ticketWithoutLimit->quantity_available);
        $this->assertSame(10, $ticketWithoutLimit->quantity_sold);

        $this->assertSame(10, $ticketWithLimit->quantity);
        $this->assertSame(5, $ticketWithLimit->quantity_available);
        $this->assertSame(5, $ticketWithLimit->quantity_sold);
    }
}
