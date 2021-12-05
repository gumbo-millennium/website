<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\EnrollNew;

use App\Facades\Enroll;
use App\Models\Activity;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Date;
use Tests\TestCase;

/**
 * Test cases:.
 *
 * ✅ Guest routes
 * ✅ Event without tickets
 * ✅ Event with one ticket
 * ✅ Event with two tickets
 * ✅ Event with sold-out tickets
 * ✅ Event with public and private tickets
 * ✅ Public event with only private tickets
 * ✅ Event with tickets not yet available
 * ✅ Ended event
 * ✅ Enroll service error
 */
class TicketControllerTest extends TestCase
{
    public function test_guest_access(): void
    {
        $activity = factory(Activity::class)->create();
        $ticket = $activity->tickets()->save(factory(Ticket::class)->create());

        $privateActivity = factory(Activity::class)->state('private')->create();
        $privateTicket = $privateActivity->tickets()->save(factory(Ticket::class)->create());

        // View routes
        $this->get(route('enroll.create', [$activity]))
            ->assertRedirect(route('login'));

        $this->get(route('enroll.create', [$privateActivity]))
            ->assertRedirect(route('login'));

        // Create routes
        $this->post(route('enroll.store', [$activity]), ['ticket_id' => $ticket->id])
            ->assertRedirect(route('login'));

        $this->post(route('enroll.store', [$privateActivity]), ['ticket_id' => $privateTicket->id])
            ->assertRedirect(route('login'));
    }

    public function test_user_access(): void
    {
        $activity = factory(Activity::class)->create();
        $ticket = $activity->tickets()->save(factory(Ticket::class)->create());

        $privateActivity = factory(Activity::class)->state('private')->create();
        $privateTicket = $privateActivity->tickets()->save(factory(Ticket::class)->create());

        // Get user
        $this->actingAs(factory(User::class)->create());

        // View routes
        $this->get(route('enroll.create', [$activity]))
            ->assertOk();

        $this->get(route('enroll.create', [$privateActivity]))
            ->assertForbidden();

        // Create routes
        $this->post(route('enroll.store', [$activity]), ['ticket_id' => $ticket->id])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('enroll.show', [$activity]));

        $this->post(route('enroll.store', [$privateActivity]), ['ticket_id' => $privateTicket->id])
            ->assertForbidden();
    }

    public function test_member_access(): void
    {
        $activity = factory(Activity::class)->create();
        $ticket = $activity->tickets()->save(factory(Ticket::class)->create());

        $privateActivity = factory(Activity::class)->state('private')->create();
        $privateTicket = $privateActivity->tickets()->save(factory(Ticket::class)->create());

        // Check member
        $this->actingAs($this->getMemberUser());

        // View routes
        $this->get(route('enroll.create', [$activity]))
            ->assertOk();

        $this->get(route('enroll.create', [$privateActivity]))
            ->assertOk();

        // Create routes
        $this->post(route('enroll.store', [$activity]), ['ticket_id' => $ticket->id])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('enroll.show', [$activity]));

        $this->post(route('enroll.store', [$privateActivity]), ['ticket_id' => $privateTicket->id])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('enroll.show', [$privateActivity]));
    }

    public function test_enroll_without_tickets(): void
    {
        $activity = factory(Activity::class)->create();

        $this->actingAs(factory(User::class)->create());

        $this->get($createRoute = route('enroll.create', [$activity]))
            ->assertOk()
            ->assertSee(__('No tickets available'));

        $this->post(route('enroll.store', [$activity]))
            ->assertSessionHasErrors()
            ->assertRedirect($createRoute);
    }

    public function test_enroll_with_one_ticket(): void
    {
        $activity = factory(Activity::class)->create();
        $activity->tickets()->save(
            $ticket = factory(Ticket::class)->create(),
        );

        $this->actingAs($user = factory(User::class)->create());

        $this->get(route('enroll.create', [$activity]))
            ->assertOk()
            ->assertSee($ticket->title);

        $this->post(route('enroll.store', [$activity]), ['ticket_id' => $ticket->id])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('enroll.show', [$activity]));

        $this->assertSame(1, $user->enrollments()->count());
    }

    public function test_enroll_with_sold_out_tickets(): void
    {
        $activity = factory(Activity::class)->create();
        $activity->tickets()->save(
            $ticket = factory(Ticket::class)->create([
                'quantity' => 1,
            ]),
        );

        // Create a user and give it the only spot remaining
        $this->actingAs($user = factory(User::class)->create());
        Enroll::createEnrollment($activity, $ticket);

        // Act as someone else
        $this->actingAs($user = factory(User::class)->create());

        // Create the enrollment with the fake ticket
        $this->get($createRoute = route('enroll.create', [$activity]))
            ->assertOk()
            ->assertSee($ticket->title)
            ->assertDontSee(__('Enroll'));

        // Order the ticket that's sold out (form manipulation baby!)
        $this->post(route('enroll.store', [$activity]), ['ticket_id' => $ticket->id])
            ->assertSessionHasErrors()
            ->assertRedirect($createRoute);

        // Check enroll was blocked
        $this->assertSame(0, $user->enrollments()->count());
    }

    public function test_enroll_with_mixed_tickets(): void
    {
        $activity = factory(Activity::class)->create();
        [$publicTicket, $privateTicket] = $activity->tickets()->saveMany([
            factory(Ticket::class)->make(),
            factory(Ticket::class)->make([
                'is_public' => false,
            ]),
        ]);

        // Act as a regular user
        $this->actingAs($user = factory(User::class)->create());

        // Create the enrollment with the fake ticket
        $this->get($createRoute = route('enroll.create', [$activity]))
            ->assertOk()
            ->assertSee($publicTicket->title)
            ->assertDontSee($privateTicket->title);

        // Order the private ticket
        $this->post(route('enroll.store', [$activity]), ['ticket_id' => $privateTicket->id])
            ->assertSessionHasErrors()
            ->assertRedirect($createRoute);

        // Order the public ticket
        $this->post(route('enroll.store', [$activity]), ['ticket_id' => $publicTicket->id])
            ->assertSessionDoesntHaveErrors()
            ->assertRedirect(route('enroll.show', [$activity]));

        // Check enroll worked
        $this->assertSame(1, $user->enrollments()->count());

        $this->actingAs($user = $this->getMemberUser());

        // Create the enrollment with the fake ticket
        $this->get($createRoute = route('enroll.create', [$activity]))
            ->assertOk()
            ->assertSee($publicTicket->title)
            ->assertSee($privateTicket->title);

        // Order the private ticket
        $this->post(route('enroll.store', [$activity]), ['ticket_id' => $privateTicket->id])
            ->assertSessionDoesntHaveErrors()
            ->assertRedirect(route('enroll.show', [$activity]));

        // Check enroll was OK
        $this->assertSame(1, $user->enrollments()->count());
    }

    public function test_enroll_with_only_private_tickets(): void
    {
        $activity = factory(Activity::class)->create();
        $privateTicket = $activity->tickets()->save(
            factory(Ticket::class)->make([
                'is_public' => false,
            ]),
        );

        // Act as a regular user
        $this->actingAs($user = factory(User::class)->create());

        // Create the enrollment with the fake ticket
        $this->get($createRoute = route('enroll.create', [$activity]))
            ->assertOk()
            ->assertDontSee($privateTicket->title);

        // Order the private ticket
        $this->post(route('enroll.store', [$activity]), ['ticket_id' => $privateTicket->id])
            ->assertSessionHasErrors()
            ->assertRedirect($createRoute);

        // Check enroll was blocked
        $this->assertSame(0, $user->enrollments()->count());

        $this->actingAs($user = $this->getMemberUser());

        // Create the enrollment with the fake ticket
        $this->get($createRoute = route('enroll.create', [$activity]))
            ->assertOk()
            ->assertSee($privateTicket->title);

        // Order the private ticket
        $this->post(route('enroll.store', [$activity]), ['ticket_id' => $privateTicket->id])
            ->assertSessionDoesntHaveErrors()
            ->assertRedirect(route('enroll.show', [$activity]));

        // Check enroll was OK
        $this->assertSame(1, $user->enrollments()->count());
    }

    public function test_enroll_with_unavailable_tickets(): void
    {
        $activity = factory(Activity::class)->create();
        [$nowTicket, $soonTicket, $laterTicket, $memberTicket] = $activity->tickets()->saveMany([
            factory(Ticket::class)->make([
                'available_from' => Date::now()->subDays(1),
            ]),
            factory(Ticket::class)->make([
                'available_from' => Date::now()->addDays(1),
            ]),
            factory(Ticket::class)->make([
                'available_from' => Date::now()->addWeek(1),
            ]),
            factory(Ticket::class)->make([
                'is_public' => false,
            ]),
        ]);

        // Act as a regular user
        $this->actingAs($user = factory(User::class)->create());

        // Create the enrollment with the fake ticket
        $this->get($createRoute = route('enroll.create', [$activity]))
            ->assertOk()
            ->assertSee($nowTicket->title)
            ->assertSee("data-test-action=\"buy-{$nowTicket->id}\"", false)

            ->assertSee($soonTicket->title)
            ->assertSee("data-test-action=\"show-{$soonTicket->id}\"", false)

            ->assertSee($laterTicket->title)
            ->assertSee("data-test-action=\"show-{$laterTicket->id}\"", false)

            ->assertDontSee($memberTicket->title)
            ->assertDontSee("data-test-action=\"buy-{$memberTicket->id}\"", false)
            ->assertDontSee("data-test-action=\"show-{$memberTicket->id}\"", false);

        // Order the not-yet-available tickets
        $this->post(route('enroll.store', [$activity]), ['ticket_id' => $soonTicket->id])
            ->assertSessionHasErrors()
            ->assertRedirect($createRoute);

        $this->post(route('enroll.store', [$activity]), ['ticket_id' => $laterTicket->id])
            ->assertSessionHasErrors()
            ->assertRedirect($createRoute);

        // Check enroll was blocked
        $this->assertSame(0, $user->enrollments()->count());

        // Order the current ticket
        $this->post(route('enroll.store', [$activity]), ['ticket_id' => $nowTicket->id])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('enroll.show', [$activity]));

        // Check enroll was OK
        $this->assertSame(1, $user->enrollments()->count());
    }

    public function test_enroll_with_no_more_seats(): void
    {
        $activity = factory(Activity::class)->state('with-tickets')->create([
            'seats' => 2,
        ]);
        $ticket = $activity->tickets->first();

        // Enroll two users
        for ($i = 0; $i < 2; $i++) {
            $this->actingAs(factory(User::class)->create());
            Enroll::createEnrollment($activity, $ticket);
        }

        // Act as a regular user
        $this->actingAs($user = factory(User::class)->create());

        // Check enrollments are closed
        $this->get($createRoute = route('enroll.create', [$activity]))
            ->assertOk()
            ->assertSee($ticket->title)
            ->assertSee("data-test-action=\"show-{$ticket->id}\"", false)

            ->assertSee(__('Sold Out'));

        $this->post(route('enroll.store', [$activity]), ['ticket_id' => $ticket->id])
            ->assertSessionHasErrors()
            ->assertRedirect($createRoute);

        // Check enroll was blocked
        $this->assertSame(0, $user->enrollments()->count());
    }

    public function test_enroll_after_start_and_end(): void
    {
        $activity = factory(Activity::class)->create([
            'start_date' => Date::now()->subDay(1),
            'end_date' => Date::now()->subHours(23),
        ]);

        $ticket = $activity->tickets()->create([
            'title' => 'Free',
        ]);

        // Act as a regular user
        $this->actingAs(factory(User::class)->create());

        // Test if ticket page is visible
        $this->get(route('enroll.create', $activity))
            ->assertRedirect(route('activity.show', $activity));

        // Test if ticket enroll action is blocked
        $this->post(route('enroll.store', $activity), ['ticket_id' => $ticket->id])
            ->assertRedirect(route('activity.show', $activity));
    }

    public function test_enroll_when_enrolled(): void
    {
        $activity = factory(Activity::class)->create();
        [$ticketOne, $ticketTwo] = $activity->tickets()->saveMany([
            factory(Ticket::class)->make(),
            factory(Ticket::class)->make(),
        ]);

        // Act as a regular user
        $this->actingAs($user = factory(User::class)->create());

        // Enroll with ticket 1
        Enroll::createEnrollment($activity, $ticketOne);

        // Check enroll was OK
        $this->assertSame(1, $user->enrollments()->count());

        // Check the ticket view
        $this->get(route('enroll.create', [$activity]))
            ->assertRedirect($showRoute = route('enroll.show', [$activity]));

        // Create a new enrollment with either tickets
        $this->post(route('enroll.store', [$activity]), ['ticket_id' => $ticketOne->id])
            ->assertSessionHasNoErrors()
            ->assertRedirect($showRoute);

        $this->post(route('enroll.store', [$activity]), ['ticket_id' => $ticketTwo->id])
            ->assertSessionHasNoErrors()
            ->assertRedirect($showRoute);

        // Test invalid ticket
        $this->post(route('enroll.store', [$activity]), ['ticket_id' => $ticketTwo->id + 1])
            ->assertSessionHasNoErrors()
            ->assertRedirect($showRoute);

        // Check enroll was not changed
        $this->assertSame(1, $user->enrollments()->count());
    }
}
