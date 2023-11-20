<?php

declare(strict_types=1);

namespace Tests\Feature\Views\Components;

use App\Helpers\Str;
use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\States\Enrollment\Paid;
use App\Models\Ticket;
use Illuminate\Testing\TestView;
use InvalidArgumentException;
use Tests\TestCase;

class TicketTest extends TestCase
{
    /**
     * Ensure a regular render works as it should.
     */
    public function test_default(): void
    {
        /** @var Ticket $ticket */
        $ticket = Ticket::factory()->forActivity()->create([
            'price' => 20_00,
        ]);

        $this->renderTicket($ticket)
            ->assertSee($ticket->title)
            ->assertDontSee(__('Members Only'))
            ->assertSee(Str::price($ticket->total_price))
            ->assertSee($ticket->description)
            ->assertSee(__('Public'))
            ->assertSee(__('No ticket limit'))
            ->assertSee(__('Enroll'));
    }

    /**
     * Test if a ticket without limits shows as such.
     */
    public function test_seat_limit_none(): void
    {
        $activity = Activity::factory()->create();
        $ticket = Ticket::factory()->for($activity)->create();

        $this->renderTicket($ticket)
            ->assertSee(__('No ticket limit'));
    }

    /**
     * Test if a ticket with just a ticket limit works.
     */
    public function test_seat_limit_ticket_only(): void
    {
        $ticket = Ticket::factory()->forActivity([
            'seats' => null,
        ])->create([
            'quantity' => 10,
        ]);

        $this->renderTicket($ticket)
            ->assertSee(__(':quantity tickets, :available left', [
                'quantity' => 10,
                'available' => 10,
            ]));
    }

    /**
     * Test if a ticket with just a ticket limit works.
     */
    public function test_seat_limit_activity_only(): void
    {
        $ticket = Ticket::factory()->for(Activity::factory()->withSeats(10))->create([
            'quantity' => null,
        ]);

        $this->renderTicket($ticket)
            ->assertSee(__(':quantity tickets, :available left', [
                'quantity' => 10,
                'available' => 10,
            ]));
    }

    /**
     * Test if a ticket where the activity's other ticket's sales
     * are affecting this ticket's availability is showing it
     * as such.
     */
    public function test_seat_limit_with_ticket_above_activity(): void
    {
        $activity = Activity::factory()->create([
            'seats' => 15,
        ]);
        [$ticket1, $ticket2] = Ticket::factory()->for($activity)->count(2)->create([
            'quantity' => 10,
        ]);

        Enrollment::factory(6)->hasUser()->for($activity)->for($ticket2)->create([
            'state' => Paid::class,
        ]);

        $this->renderTicket($ticket1)
            ->assertSee(__(':quantity tickets, :available left', [
                'quantity' => 10,
                'available' => 9,
            ]));
    }

    private function renderTicket(Ticket $ticket): TestView
    {
        throw_unless($ticket->activity, new InvalidArgumentException('Ticket has no activity'));

        return $this->blade(
            '<x-enroll.ticket :ticket="$ticket" />',
            [
                'ticket' => $ticket,
            ],
        );
    }
}
