<?php

declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Models\Activity;
use App\Models\Ticket;
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
        $activity = factory(Activity::class)->create();

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
        $activity = factory(Activity::class)->create();

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
        $activity = factory(Activity::class)->create([
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
}
